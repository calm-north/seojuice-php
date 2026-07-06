<?php

/**
 * Server-side rendering injection patterns for SEOJuice.
 *
 * Demonstrates plain PHP output buffering, a PSR-15 middleware,
 * and a simple in-memory TTL cache for suggestion data.
 *
 * SeoInjector::inject() is stateless and fail-open: it always returns valid
 * HTML — on a malformed/empty API response, an exception, or an unexpectedly
 * short result, it returns the original HTML untouched.
 *
 * Requirements:
 *     composer require seojuice/seojuice
 *
 * For PSR-15 middleware (Slim, Mezzio, etc.):
 *     composer require psr/http-server-middleware
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SEOJuice\Injection\SeoInjector;
use SEOJuice\SEOJuice;

// ---------------------------------------------------------------------------
// 1. Simple TTL cache for the raw /suggestions payload
// ---------------------------------------------------------------------------

class SuggestionsCache
{
    private const TTL = 300; // 5 minutes

    /** @var array<string, array{data: array<string, mixed>, expires: float}> */
    private static array $store = [];

    /**
     * @return array<string, mixed>|null
     */
    public static function get(string $url): ?array
    {
        $entry = self::$store[$url] ?? null;
        if ($entry === null || $entry['expires'] < microtime(true)) {
            unset(self::$store[$url]);
            return null;
        }
        return $entry['data'];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function set(string $url, array $data): void
    {
        self::$store[$url] = [
            'data' => $data,
            'expires' => microtime(true) + self::TTL,
        ];
    }
}

// ---------------------------------------------------------------------------
// 2. Plain PHP — output buffering pattern
// ---------------------------------------------------------------------------

/**
 * Render a page with SEO injection applied.
 *
 * @param callable(): void $render Callback that echoes HTML output
 */
function renderWithSeo(string $url, callable $render): string
{
    ob_start();
    $render();
    $html = ob_get_clean();

    if ($html === false || $html === '') {
        return '';
    }

    $data = SuggestionsCache::get($url);

    if ($data === null) {
        $client = new SEOJuice(getenv('SEOJUICE_API_KEY'));
        $data = $client->smart()->suggestions($url); // returns [] on any failure
        SuggestionsCache::set($url, $data);
    }

    $injector = new SeoInjector();
    return $injector->inject($html, $data); // no-op on empty/invalid $data
}

// ---------------------------------------------------------------------------
// 3. PSR-15 Middleware — works with Slim, Mezzio, any PSR-15 framework
// ---------------------------------------------------------------------------

class SeoInjectionMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly SEOJuice $client) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $response = $handler->handle($request);

        $contentType = $response->getHeaderLine('Content-Type');
        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        $url = (string) $request->getUri();
        $data = SuggestionsCache::get($url);

        if ($data === null) {
            $data = $this->client->smart()->suggestions($url); // returns [] on any failure
            SuggestionsCache::set($url, $data);
        }

        $injector = new SeoInjector();
        $body = (string) $response->getBody();
        $modified = $injector->inject($body, $data); // no-op on empty/invalid $data

        $stream = \GuzzleHttp\Psr7\Utils::streamFor($modified);
        return $response->withBody($stream);
    }
}

// ---------------------------------------------------------------------------
// Usage example
// ---------------------------------------------------------------------------

$html = renderWithSeo('https://example.com/blog/seo-guide', function (): void {
    echo '<html><head><title>My Blog Post</title></head>';
    echo '<body><h1>SEO Guide</h1><p>Content here...</p></body></html>';
});

echo $html;
