<?php

/**
 * Laravel 10+/11+ integration patterns for SEOJuice.
 *
 * Demonstrates service provider, middleware, controller, and Artisan command
 * for injecting SEO data into Laravel applications.
 *
 * Requirements:
 *     composer require seojuice/seojuice
 *
 * Register in config/services.php:
 *     'seojuice' => [
 *         'api_key' => env('SEOJUICE_API_KEY'),
 *     ],
 *
 * Register middleware in bootstrap/app.php (Laravel 11):
 *     ->withMiddleware(function (Middleware $middleware) {
 *         $middleware->web(append: [SeoInjectionMiddleware::class]);
 *     })
 *
 * Or in app/Http/Kernel.php (Laravel 10):
 *     protected $middlewareGroups = [
 *         'web' => [
 *             // ...
 *             \App\SeoInjectionMiddleware::class,
 *         ],
 *     ];
 */

declare(strict_types=1);

namespace App;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;
use SEOJuice\Enums\Period;
use SEOJuice\Injection\SeoInjector;
use SEOJuice\SEOJuice;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

// ---------------------------------------------------------------------------
// 1. Service Provider
// ---------------------------------------------------------------------------

class SEOJuiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SEOJuice::class, function ($app): SEOJuice {
            return new SEOJuice(config('services.seojuice.api_key'));
        });
    }
}

// ---------------------------------------------------------------------------
// 2. Middleware — automatic SEO tag injection
// ---------------------------------------------------------------------------

class SeoInjectionMiddleware
{
    public function __construct(private readonly SEOJuice $client) {}

    public function handle(Request $request, Closure $next): BaseResponse
    {
        /** @var Response $response */
        $response = $next($request);

        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if ($content === false) {
            return $response;
        }

        $suggestions = $this->client->smart()->suggestions($request->url());
        if ($suggestions->isEmpty()) {
            return $response;
        }

        $injector = new SeoInjector();
        $response->setContent($injector->inject($content, $suggestions));

        return $response;
    }
}

// ---------------------------------------------------------------------------
// 3. Controller — fetch intelligence data for a view
// ---------------------------------------------------------------------------

class SeoController
{
    public function dashboard(Request $request, SEOJuice $client): Response
    {
        $domain = $request->query('domain', 'example.com');

        $summary = $client->intelligence($domain)->summary(
            period: Period::ThirtyDays,
            includeTrends: true,
        );
        $aiso = $client->aiso($domain)->get(Period::ThirtyDays);

        return new Response(view('seo.dashboard', [
            'domain' => $domain,
            'summary' => $summary,
            'aiso' => $aiso,
        ]));
    }
}

// ---------------------------------------------------------------------------
// 4. Artisan Command — check content decay
// ---------------------------------------------------------------------------

class CheckContentDecay
{
    public function handle(SEOJuice $client): int
    {
        $domain = 'example.com';

        $result = $client->content($domain)->listDecayAlerts(
            isActive: true,
            severity: 'high',
        );

        foreach ($result->results as $alert) {
            echo "[{$alert->decayType}] {$alert->pageUrl} — severity: {$alert->severity}\n";
        }

        echo "Found " . count($result->results) . " high-severity alerts.\n";
        return 0;
    }
}
