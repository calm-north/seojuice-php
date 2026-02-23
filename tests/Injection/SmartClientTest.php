<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Injection;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;
use PHPUnit\Framework\TestCase;
use SEOJuice\Config;
use SEOJuice\Exceptions\SEOJuiceException;
use SEOJuice\Injection\SmartClient;
use SEOJuice\Injection\Suggestions;

final class SmartClientTest extends TestCase
{
    /** @var array<int, array<string, mixed>> */
    private array $history = [];

    private function createSmartClient(MockHandler $mock): SmartClient
    {
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($this->history));

        $guzzleClient = new Client(['handler' => $handlerStack]);

        $config = new Config(smartUrl: 'https://smart.test.io');

        return new SmartClient($config, $guzzleClient);
    }

    public function testSuggestionsReturnsSuggestionsObject(): void
    {
        $responseData = [
            'links' => [],
            'images' => [],
            'meta_tags' => ['title' => 'Test'],
            'structured_data' => [],
            'accessibility_fixes' => [],
            'og_tags' => [],
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($responseData)),
        ]);

        $client = $this->createSmartClient($mock);
        $result = $client->suggestions('https://example.com/page');

        $this->assertInstanceOf(Suggestions::class, $result);
        $this->assertSame('Test', $result->metaTags['title']);
    }

    public function testSuggestionsSendsUrlAsQueryParam(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'links' => [],
                'images' => [],
                'meta_tags' => [],
                'structured_data' => [],
                'accessibility_fixes' => [],
                'og_tags' => [],
            ])),
        ]);

        $client = $this->createSmartClient($mock);
        $client->suggestions('https://example.com/my-page');

        $this->assertCount(1, $this->history);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertStringContainsString('url=', (string) $request->getUri());
        $this->assertStringContainsString('example.com', (string) $request->getUri());
    }

    public function testSuggestionsThrowsSeoJuiceExceptionOnNetworkError(): void
    {
        $mock = new MockHandler([
            new ConnectException(
                'Connection refused',
                new Request('GET', 'https://smart.test.io/suggestions'),
            ),
        ]);

        $client = $this->createSmartClient($mock);

        $this->expectException(SEOJuiceException::class);
        $this->expectExceptionMessageMatches('/Failed to fetch suggestions/');

        $client->suggestions('https://example.com');
    }

    public function testSuggestionsExceptionHasSmartClientErrorCode(): void
    {
        $mock = new MockHandler([
            new ConnectException(
                'Timeout',
                new Request('GET', 'https://smart.test.io/suggestions'),
            ),
        ]);

        $client = $this->createSmartClient($mock);

        try {
            $client->suggestions('https://example.com');
            $this->fail('Expected SEOJuiceException');
        } catch (SEOJuiceException $e) {
            $this->assertSame('smart_client_error', $e->errorCode);
        }
    }

    public function testSuggestionsHandlesFullResponseData(): void
    {
        $responseData = [
            'links' => [['href' => '/about', 'anchor' => 'About']],
            'images' => [['src' => '/img.jpg', 'alt' => 'Test']],
            'meta_tags' => ['title' => 'Page Title', 'description' => 'Desc'],
            'structured_data' => [['@type' => 'WebSite']],
            'accessibility_fixes' => [['type' => 'aria']],
            'og_tags' => ['title' => 'OG Title'],
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($responseData)),
        ]);

        $client = $this->createSmartClient($mock);
        $result = $client->suggestions('https://example.com');

        $this->assertFalse($result->isEmpty());
        $this->assertCount(1, $result->links);
        $this->assertCount(1, $result->images);
        $this->assertCount(1, $result->structuredData);
        $this->assertCount(1, $result->accessibilityFixes);
        $this->assertSame('Page Title', $result->metaTags['title']);
        $this->assertSame('OG Title', $result->ogTags['title']);
    }
}
