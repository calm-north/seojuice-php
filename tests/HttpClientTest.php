<?php

declare(strict_types=1);

namespace SEOJuice\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;
use PHPUnit\Framework\TestCase;
use SEOJuice\Config;
use SEOJuice\Exceptions\AuthException;
use SEOJuice\Exceptions\ForbiddenException;
use SEOJuice\Exceptions\NotFoundException;
use SEOJuice\Exceptions\RateLimitException;
use SEOJuice\Exceptions\SEOJuiceException;
use SEOJuice\Exceptions\ServerException;
use SEOJuice\Exceptions\ValidationException;
use SEOJuice\HttpClient;

final class HttpClientTest extends TestCase
{
    /** @var array<int, array<string, mixed>> */
    private array $history = [];

    private function createHttpClient(MockHandler $mock): HttpClient
    {
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($this->history));

        $guzzleClient = new Client(['handler' => $handlerStack]);

        $config = new Config(baseUrl: 'https://api.test.com/v2');

        return new HttpClient('test-api-key', $config, $guzzleClient);
    }

    public function testGetSendsGetRequestToCorrectUrl(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 'ok'])),
        ]);

        $client = $this->createHttpClient($mock);
        $client->get('websites/');

        $this->assertCount(1, $this->history);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://api.test.com/v2/websites/', (string) $request->getUri());
    }

    public function testGetAppendsQueryParamsToUrl(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['results' => []])),
        ]);

        $client = $this->createHttpClient($mock);
        $client->get('websites/example.com/pages/', [
            'page' => 2,
            'page_size' => 10,
        ]);

        $request = $this->history[0]['request'];
        $uri = (string) $request->getUri();
        $this->assertStringContainsString('page=2', $uri);
        $this->assertStringContainsString('page_size=10', $uri);
    }

    public function testGetFiltersNullQueryParams(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['results' => []])),
        ]);

        $client = $this->createHttpClient($mock);
        $client->get('websites/', [
            'page' => 1,
            'filter' => null,
        ]);

        $request = $this->history[0]['request'];
        $uri = (string) $request->getUri();
        $this->assertStringContainsString('page=1', $uri);
        $this->assertStringNotContainsString('filter', $uri);
    }

    public function testGetReturnsDecodedJsonResponse(): void
    {
        $responseData = ['domain' => 'example.com', 'seo_score' => 85.5];
        $mock = new MockHandler([
            new Response(200, [], json_encode($responseData)),
        ]);

        $client = $this->createHttpClient($mock);
        $result = $client->get('websites/example.com/');

        $this->assertSame($responseData, $result);
    }

    public function testPostSendsPostWithJsonBody(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['id' => 1])),
        ]);

        $client = $this->createHttpClient($mock);
        $client->post('websites/', ['domain' => 'example.com']);

        $this->assertCount(1, $this->history);

        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://api.test.com/v2/websites/', (string) $request->getUri());

        $body = json_decode((string) $request->getBody(), true);
        $this->assertSame(['domain' => 'example.com'], $body);
    }

    public function testPostReturnsDecodedJsonResponse(): void
    {
        $responseData = ['id' => 42, 'status' => 'created'];
        $mock = new MockHandler([
            new Response(201, [], json_encode($responseData)),
        ]);

        $client = $this->createHttpClient($mock);
        $result = $client->post('websites/', ['domain' => 'new.com']);

        $this->assertSame($responseData, $result);
    }

    public function testGetRawReturnsRawStringBody(): void
    {
        $rawContent = '<html><body>Hello</body></html>';
        $mock = new MockHandler([
            new Response(200, [], $rawContent),
        ]);

        $client = $this->createHttpClient($mock);
        $result = $client->getRaw('websites/example.com/export/');

        $this->assertSame($rawContent, $result);
    }

    public function testGetRawSendsGetRequestWithQueryParams(): void
    {
        $mock = new MockHandler([
            new Response(200, [], 'raw-data'),
        ]);

        $client = $this->createHttpClient($mock);
        $client->getRaw('export/', ['format' => 'csv']);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertStringContainsString('format=csv', (string) $request->getUri());
    }

    public function test401ResponseThrowsAuthException(): void
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode(['detail' => 'Invalid API key'])),
        ]);

        $client = $this->createHttpClient($mock);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Invalid API key');

        $client->get('websites/');
    }

    public function test403ResponseThrowsForbiddenException(): void
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode(['detail' => 'Access denied'])),
        ]);

        $client = $this->createHttpClient($mock);

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Access denied');

        $client->get('websites/');
    }

    public function test404ResponseThrowsNotFoundException(): void
    {
        $mock = new MockHandler([
            new Response(404, [], json_encode(['detail' => 'Not found'])),
        ]);

        $client = $this->createHttpClient($mock);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not found');

        $client->get('websites/nonexistent.com/');
    }

    public function test429ResponseThrowsRateLimitException(): void
    {
        $mock = new MockHandler([
            new Response(429, [], json_encode(['detail' => 'Rate limit exceeded'])),
        ]);

        $client = $this->createHttpClient($mock);

        $this->expectException(RateLimitException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $client->get('websites/');
    }

    public function test400ResponseThrowsValidationException(): void
    {
        $mock = new MockHandler([
            new Response(400, [], json_encode(['detail' => 'Invalid domain format'])),
        ]);

        $client = $this->createHttpClient($mock);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid domain format');

        $client->post('websites/', ['domain' => '']);
    }

    public function test422ResponseThrowsValidationException(): void
    {
        $mock = new MockHandler([
            new Response(422, [], json_encode(['detail' => 'Unprocessable entity'])),
        ]);

        $client = $this->createHttpClient($mock);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unprocessable entity');

        $client->post('websites/', []);
    }

    public function test500ResponseThrowsServerException(): void
    {
        $mock = new MockHandler([
            new Response(500, [], json_encode(['detail' => 'Internal server error'])),
        ]);

        $client = $this->createHttpClient($mock);

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Internal server error');

        $client->get('websites/');
    }

    public function test502ResponseThrowsServerException(): void
    {
        $mock = new MockHandler([
            new Response(502, [], json_encode(['detail' => 'Bad gateway'])),
        ]);

        $client = $this->createHttpClient($mock);

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Bad gateway');

        $client->get('websites/');
    }

    public function test503ResponseThrowsServerException(): void
    {
        $mock = new MockHandler([
            new Response(503, [], json_encode(['detail' => 'Service unavailable'])),
        ]);

        $client = $this->createHttpClient($mock);

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Service unavailable');

        $client->get('websites/');
    }

    public function testNetworkErrorThrowsSeoJuiceExceptionWithNetworkErrorCode(): void
    {
        $mock = new MockHandler([
            new ConnectException(
                'Connection timed out',
                new Request('GET', 'https://api.test.com/v2/websites/'),
            ),
        ]);

        $client = $this->createHttpClient($mock);

        try {
            $client->get('websites/');
            $this->fail('Expected SEOJuiceException was not thrown');
        } catch (SEOJuiceException $e) {
            $this->assertSame('network_error', $e->errorCode);
            $this->assertStringContainsString('Connection timed out', $e->getMessage());
        }
    }

    public function testErrorResponseExtractsMessageFromDetailField(): void
    {
        $mock = new MockHandler([
            new Response(404, [], json_encode(['detail' => 'Website not found'])),
        ]);

        $client = $this->createHttpClient($mock);

        try {
            $client->get('websites/missing.com/');
            $this->fail('Expected NotFoundException');
        } catch (NotFoundException $e) {
            $this->assertSame('Website not found', $e->getMessage());
        }
    }

    public function testErrorResponseExtractsMessageFromMessageField(): void
    {
        $mock = new MockHandler([
            new Response(400, [], json_encode(['message' => 'Bad request data'])),
        ]);

        $client = $this->createHttpClient($mock);

        try {
            $client->post('websites/', []);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame('Bad request data', $e->getMessage());
        }
    }

    public function testErrorResponseExtractsErrorCode(): void
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode([
                'detail' => 'Token expired',
                'error_code' => 'token_expired',
            ])),
        ]);

        $client = $this->createHttpClient($mock);

        try {
            $client->get('websites/');
            $this->fail('Expected AuthException');
        } catch (AuthException $e) {
            $this->assertSame('token_expired', $e->errorCode);
        }
    }

    public function testErrorResponseDefaultsToUnknownErrorCode(): void
    {
        $mock = new MockHandler([
            new Response(404, [], json_encode(['detail' => 'Not found'])),
        ]);

        $client = $this->createHttpClient($mock);

        try {
            $client->get('websites/missing.com/');
            $this->fail('Expected NotFoundException');
        } catch (NotFoundException $e) {
            $this->assertSame('unknown', $e->errorCode);
        }
    }

    public function testDetailFieldTakesPriorityOverMessageField(): void
    {
        $mock = new MockHandler([
            new Response(400, [], json_encode([
                'detail' => 'Detail message',
                'message' => 'Message field',
            ])),
        ]);

        $client = $this->createHttpClient($mock);

        try {
            $client->post('websites/', []);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame('Detail message', $e->getMessage());
        }
    }

    public function testPostErrorMappingWorks(): void
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode(['detail' => 'Unauthorized'])),
        ]);

        $client = $this->createHttpClient($mock);

        $this->expectException(AuthException::class);
        $client->post('websites/', ['domain' => 'test.com']);
    }

    public function testGetRawErrorMappingWorks(): void
    {
        $mock = new MockHandler([
            new Response(500, [], json_encode(['detail' => 'Server error'])),
        ]);

        $client = $this->createHttpClient($mock);

        $this->expectException(ServerException::class);
        $client->getRaw('export/');
    }
}
