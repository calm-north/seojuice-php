<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Injection;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use SEOJuice\Config;
use SEOJuice\Injection\SmartClient;

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

    public function testSuggestionsReturnsRawPayloadArray(): void
    {
        $responseData = ['title' => 'Test', 'suggestions' => []];

        $mock = new MockHandler([
            new Response(200, [], json_encode($responseData)),
        ]);

        $client = $this->createSmartClient($mock);
        $result = $client->suggestions('https://example.com/page');

        $this->assertIsArray($result);
        $this->assertSame('Test', $result['title']);
    }

    public function testSuggestionsSendsUrlAsQueryParam(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['title' => ''])),
        ]);

        $client = $this->createSmartClient($mock);
        $client->suggestions('https://example.com/my-page');

        $this->assertCount(1, $this->history);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertStringContainsString('url=', (string) $request->getUri());
        $this->assertStringContainsString('example.com', (string) $request->getUri());
    }

    public function testSuggestionsReturnsEmptyArrayOnNetworkError(): void
    {
        $mock = new MockHandler([
            new ConnectException(
                'Connection refused',
                new Request('GET', 'https://smart.test.io/suggestions'),
            ),
        ]);

        $client = $this->createSmartClient($mock);

        $this->assertSame([], $client->suggestions('https://example.com'));
    }

    public function testSuggestionsReturnsEmptyArrayOnInvalidJson(): void
    {
        $mock = new MockHandler([
            new Response(200, [], 'not json'),
        ]);

        $client = $this->createSmartClient($mock);

        $this->assertSame([], $client->suggestions('https://example.com'));
    }

    public function testSuggestionsHandlesFullResponseData(): void
    {
        $responseData = [
            'suggestions' => [['keyword' => 'about', 'url' => '/about', 'id' => 1]],
            'images' => [['url' => '/img.jpg', 'alt_text' => 'Test']],
            'title' => 'Page Title',
            'meta_description' => 'Desc',
            'structured_data' => json_encode(json_encode(['@type' => 'WebSite'])),
            'og_title' => 'OG Title',
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($responseData)),
        ]);

        $client = $this->createSmartClient($mock);
        $result = $client->suggestions('https://example.com');

        $this->assertCount(1, $result['suggestions']);
        $this->assertCount(1, $result['images']);
        $this->assertSame('Page Title', $result['title']);
        $this->assertSame('OG Title', $result['og_title']);
    }

    public function testSuggestionsLogsWarningOnServerErrorButStillReturnsEmptyArray(): void
    {
        $mock = new MockHandler([
            new Response(500, [], json_encode(['detail' => 'boom'])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        $config = new Config(smartUrl: 'https://smart.test.io');

        $records = [];
        $logger = new class($records) extends AbstractLogger {
            /** @param array<int, string> $records */
            public function __construct(private array &$records) {}
            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = (string) $level;
            }
        };

        $client = new SmartClient($config, $guzzleClient, $logger);

        $this->assertSame([], $client->suggestions('https://example.com'));
        $this->assertContains('warning', $records);
    }

    public function testSuggestionsWithoutLoggerStillReturnsEmptyArrayOnError(): void
    {
        $mock = new MockHandler([
            new Response(500, [], 'boom'),
        ]);

        $client = $this->createSmartClient($mock);

        $this->assertSame([], $client->suggestions('https://example.com'));
    }
}
