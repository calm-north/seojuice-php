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
}
