<?php

declare(strict_types=1);

namespace SEOJuice\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SEOJuice\Config;
use SEOJuice\Injection\SeoInjector;
use SEOJuice\Injection\SmartClient;

final class DocsExamplesTest extends TestCase
{
    /**
     * Every shipped example must be syntactically valid PHP (autoloads /
     * symbol-resolves against the published package). Guards item 5.
     */
    public function testAllExamplesAreSyntacticallyValid(): void
    {
        $dir = __DIR__ . '/../examples';
        $files = glob($dir . '/*.php');
        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            $output = [];
            $exit = 0;
            exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $exit);
            $this->assertSame(0, $exit, "Syntax error in {$file}: " . implode("\n", $output));
        }
    }

    /**
     * Executes the array-form README SSR + Laravel pattern against a mock
     * transport. A README block calling ->isEmpty() on an array (the pre-1.4.1
     * bug) would fatal here. Guards items 2-3.
     */
    public function testReadmeSsrArrayPatternRunsWithoutTypeError(): void
    {
        $payload = [
            'suggestions' => [],
            'images' => [],
            'title' => 'Doc Title',
            'meta_description' => 'Doc description',
        ];
        $mock = new MockHandler([
            new Response(200, [], json_encode($payload)),
        ]);
        $guzzleClient = new Client(['handler' => HandlerStack::create($mock)]);
        $smart = new SmartClient(new Config(smartUrl: 'https://smart.test.io'), $guzzleClient);

        // The exact README pattern (array form).
        $data = $smart->suggestions('https://example.com/blog/post');

        $html = '<html><head><title>Old</title></head><body><h1>Hi</h1><p>Body</p></body></html>';
        if ($data !== []) {
            $html = (new SeoInjector())->inject($html, $data);
        }

        $this->assertIsArray($data);
        $this->assertStringContainsString('<body', $html);
    }

    /**
     * The README webhook fence must reject an unset secret. Guards items 1-2.
     */
    public function testReadmeWebhookFenceRejectsEmptySecret(): void
    {
        $body = '{"event":"change.created"}';
        $forged = hash_hmac('sha256', $body, '');

        $this->assertFalse(\SEOJuice\Webhooks::verifySignature('', $body, $forged));
    }

    /**
     * Every ->method(...) / Class::method(...) call in README fences and
     * examples that names an SDK method must match a real signature: named
     * arguments exist and the argument count fits. A method name shared by
     * several SDK classes passes when any one of them fits.
     */
    public function testReadmeAndExampleCallsMatchRealSignatures(): void
    {
        $sources = array_merge(
            [__DIR__ . '/../README.md'],
            glob(__DIR__ . '/../examples/*.php') ?: [],
        );
        $methods = $this->sdkMethods();
        $this->assertNotEmpty($methods, "no SDK classes reflected");
        $problems = [];

        foreach ($sources as $file) {
            $text = (string) file_get_contents($file);
            if (str_ends_with($file, '.md')) {
                preg_match_all('/```php\n(.*?)```/s', $text, $m);
                $text = implode("\n", $m[1]);
            }
            foreach ($this->extractCalls($text) as [$name, $args]) {
                if (!isset($methods[$name])) {
                    continue;
                }
                $named = [];
                $positional = 0;
                foreach ($args as $arg) {
                    if (preg_match('/^\s*([A-Za-z_]\w*)\s*:(?!:)/', $arg, $nm)) {
                        $named[] = $nm[1];
                    } else {
                        $positional++;
                    }
                }
                $fits = false;
                foreach ($methods[$name] as $ref) {
                    if ($this->signatureFits($ref, $named, $positional)) {
                        $fits = true;
                        break;
                    }
                }
                if (!$fits) {
                    $problems[] = basename($file) . ": {$name}(" . count($args) . ' args, named: ' . implode(',', $named) . ')';
                }
            }
        }

        $this->assertSame([], $problems, "Calls that match no SDK signature:\n" . implode("\n", $problems));
    }

    /** @return array<string, array<int, \ReflectionMethod>> */
    private function sdkMethods(): array
    {
        $out = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/../src'));
        foreach ($it as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $rel = substr($file->getRealPath(), strlen(realpath(__DIR__ . '/../src')) + 1);
            $class = 'SEOJuice\\' . str_replace(['/', '.php'], ['\\', ''], $rel);
            if (!class_exists($class)) {
                continue;
            }
            foreach ((new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $out[$method->getName()][] = $method;
            }
        }

        return $out;
    }

    /**
     * @param array<int, string> $named
     */
    private function signatureFits(\ReflectionMethod $ref, array $named, int $positional): bool
    {
        $params = $ref->getParameters();
        $names = array_map(fn ($p) => $p->getName(), $params);
        foreach ($named as $n) {
            if (!in_array($n, $names, true) && !$ref->isVariadic()) {
                return false;
            }
        }
        if (!$ref->isVariadic() && $positional + count($named) > count($params)) {
            return false;
        }
        foreach ($params as $i => $p) {
            if (!$p->isOptional() && $i >= $positional && !in_array($p->getName(), $named, true)) {
                return false;
            }
        }

        return true;
    }

    /** @return array<int, array{0: string, 1: array<int, string>}> */
    private function extractCalls(string $code): array
    {
        $calls = [];
        $code = preg_replace(['#/\*.*?\*/#s', '#(^|\s)//[^\n]*#m'], ['', '$1'], $code) ?? $code;
        preg_match_all('/(?:->|::)([A-Za-z_]\w*)\s*\(/', $code, $m, PREG_OFFSET_CAPTURE);
        foreach ($m[1] as $i => [$name]) {
            $pos = $m[0][$i][1] + strlen($m[0][$i][0]);
            $depth = 1;
            $args = [];
            $cur = '';
            $quote = null;
            for ($j = $pos, $len = strlen($code); $j < $len; $j++) {
                $c = $code[$j];
                if ($quote !== null) {
                    $cur .= $c;
                    if ($c === '\\') {
                        $cur .= $code[++$j] ?? '';
                    } elseif ($c === $quote) {
                        $quote = null;
                    }
                    continue;
                }
                if ($c === '"' || $c === "'") {
                    $quote = $c;
                } elseif ($c === '(' || $c === '[' || $c === '{') {
                    $depth++;
                } elseif ($c === ')' || $c === ']' || $c === '}') {
                    $depth--;
                    if ($depth === 0) {
                        break;
                    }
                } elseif ($c === ',' && $depth === 1) {
                    $args[] = $cur;
                    $cur = '';
                    continue;
                }
                $cur .= $c;
            }
            if (trim($cur) !== '') {
                $args[] = $cur;
            }
            $calls[] = [$name, $args];
        }

        return $calls;
    }
}
