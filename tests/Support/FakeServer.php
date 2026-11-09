<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Support;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;
use SEOJuice\Config;
use SEOJuice\HttpClient;

/**
 * Transport-level fake. Routes are "METHOD /path" (relative to the API base
 * URL); a request that matches no route fails the test. Each route callback
 * receives the decoded JSON body and the parsed query, and returns
 * [status, payload]. Callbacks encode the server's validation rules, with the
 * server file and line cited at the call site.
 */
final class FakeServer
{
    /** @var array<int, array{method: string, path: string, query: array<string, mixed>, body: mixed}> */
    public array $requests = [];

    public const FIXTURE_DIR = __DIR__ . '/../fixtures/api-fixtures';
    public const DOMAIN = 'fixtures.example';

    /**
     * Server defaults (partners/views.py paginate_queryset default_page_size=10;
     * period default "30d"; similar limit). A request value equal to the server
     * default is the same request as omitting it.
     */
    private const SERVER_DEFAULT_PARAMS = ['page' => '1', 'period' => '30d', 'limit' => '10'];

    /**
     * page_size default per route: default_page_size= in partners/views.py
     * (ReportCollectionView 12, PageSearchStatsView and PageMetricsHistoryView 30,
     * the keyword, cluster, change, backlink, decay, gap, competitor, accessibility
     * and GBP list views 20) and actionitems/api_views.py (list 20, groups 50).
     * Routes without an entry use paginate_queryset's base default of 10.
     */
    private const PAGE_SIZE_BY_ROUTE = [
        '#/reports/$#' => '12',
        '#/(search-stats|metrics-history)/$#' => '30',
        '#/action-items/groups/$#' => '50',
        '#/(action-items|clusters|content-gaps|content-pieces|competitors|keywords|backlinks|backlink-domains|accessibility-issues|changes|content-decay|gbp/reviews)/$#' => '20',
    ];

    private static function defaultPageSize(string $path): string
    {
        foreach (self::PAGE_SIZE_BY_ROUTE as $re => $size) {
            if (preg_match($re, $path) === 1) {
                return $size;
            }
        }

        return '10';
    }

    /**
     * Serves exactly one recorded server response. The request must equal the
     * fixture's method, path, query and JSON body; anything else fails the test.
     */
    public static function forFixture(string $name): self
    {
        $fx = json_decode((string) file_get_contents(self::FIXTURE_DIR . "/{$name}.json"), true, 512, JSON_THROW_ON_ERROR);
        $req = $fx['request'];
        $path = str_replace('{domain}', $req['domain'], $req['path']);
        foreach ($req['path_params'] as $k => $v) {
            $path = str_replace('{' . $k . '}', (string) $v, $path);
        }
        $wantQuery = self::canonical(array_map('strval', $req['query'] ?? []));
        $wantBody = self::canonical($req['body'] ?? []);
        $resp = $fx['response'];

        $server = new self([
            $req['method'] . ' ' . $path => function ($body, array $query) use ($path, $wantQuery, $wantBody, $resp, $name) {
                foreach (self::SERVER_DEFAULT_PARAMS + ['page_size' => self::defaultPageSize($path)] as $k => $default) {
                    if (($query[$k] ?? null) === $default) {
                        unset($query[$k]);
                    }
                }
                Assert::assertSame($wantQuery, self::canonical($query), "{$name}: query differs from the recorded request");
                Assert::assertSame($wantBody, self::canonical($body === null ? [] : json_decode(json_encode($body), true)), "{$name}: body differs from the recorded request");

                $payload = isset($resp['body_base64']) ? base64_decode($resp['body_base64']) : $resp['body'];

                return [$resp['status'], $payload];
            },
        ]);
        $server->fixture = $fx;

        return $server;
    }

    /** @var array<string, mixed> */
    public array $fixture = [];

    /** @return array<string, mixed> */
    private static function canonical(mixed $v): mixed
    {
        if (is_array($v)) {
            $v = array_map([self::class, 'canonical'], $v);
            if (!array_is_list($v)) {
                ksort($v);
            }
        }

        return $v;
    }

    /** @param array<string, callable(mixed, array<string, mixed>): array{0: int, 1: mixed}> $routes */
    public function __construct(private readonly array $routes)
    {
    }

    public function http(): HttpClient
    {
        $handler = function (RequestInterface $request) {
            $path = substr($request->getUri()->getPath(), strlen('/v2'));
            parse_str($request->getUri()->getQuery(), $query);
            $raw = (string) $request->getBody();
            $body = $raw === '' ? null : json_decode($raw, false);
            $this->requests[] = [
                'method' => $request->getMethod(),
                'path' => $path,
                'query' => $query,
                'body' => $body,
            ];

            $key = $request->getMethod() . ' ' . $path;
            if (!isset($this->routes[$key])) {
                Assert::fail("Unexpected request: {$key}");
            }
            [$status, $payload] = ($this->routes[$key])($body, $query);

            return \GuzzleHttp\Promise\Create::promiseFor(
                new Response($status, ['Content-Type' => 'application/json'], is_string($payload) ? $payload : json_encode($payload)),
            );
        };

        $client = new Client(['handler' => HandlerStack::create($handler)]);

        return new HttpClient('test-key', new Config(baseUrl: 'https://api.test/v2'), $client);
    }
}
