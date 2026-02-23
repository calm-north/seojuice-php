<?php

/**
 * Symfony 6.x/7.x integration patterns for SEOJuice.
 *
 * Demonstrates event subscriber, Twig extension, and controller patterns
 * for injecting SEO data into Symfony applications.
 *
 * Requirements:
 *     composer require seojuice/seojuice
 *
 * Service configuration (config/services.yaml):
 *     SEOJuice\SEOJuice:
 *         arguments:
 *             $apiKey: '%env(SEOJUICE_API_KEY)%'
 *
 *     App\EventSubscriber\SeoInjectionSubscriber:
 *         arguments:
 *             $client: '@SEOJuice\SEOJuice'
 *
 *     App\Twig\SEOJuiceTwigExtension:
 *         arguments:
 *             $client: '@SEOJuice\SEOJuice'
 *         tags: ['twig.extension']
 */

declare(strict_types=1);

namespace App\EventSubscriber;

use SEOJuice\Enums\Period;
use SEOJuice\Injection\SeoInjector;
use SEOJuice\SEOJuice;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

// ---------------------------------------------------------------------------
// 1. Event Subscriber — automatic SEO injection on kernel.response
// ---------------------------------------------------------------------------

class SeoInjectionSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly SEOJuice $client) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onResponse'];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $contentType = $response->headers->get('Content-Type', '');

        if (!str_contains($contentType, 'text/html')) {
            return;
        }

        $url = $event->getRequest()->getUri();
        $suggestions = $this->client->smart()->suggestions($url);

        $content = $response->getContent();
        if ($suggestions->isEmpty() || $content === false) {
            return;
        }

        $injector = new SeoInjector();
        $response->setContent($injector->inject($content, $suggestions));
    }
}

// ---------------------------------------------------------------------------
// 2. Twig Extension — seojuice_intelligence() function
// ---------------------------------------------------------------------------

class SEOJuiceTwigExtension extends AbstractExtension
{
    public function __construct(private readonly SEOJuice $client) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('seojuice_intelligence', [$this, 'getIntelligence']),
        ];
    }

    /** @return array<string, mixed> */
    public function getIntelligence(string $domain): array
    {
        $summary = $this->client->intelligence($domain)->summary(Period::ThirtyDays);

        return [
            'seo_score' => $summary->seoScore,
            'aiso_score' => $summary->aisoScore,
            'total_pages' => $summary->totalPages,
            'orphan_pages' => $summary->orphanPages,
        ];
    }
}

// ---------------------------------------------------------------------------
// 3. Controller — return intelligence data as JSON
// ---------------------------------------------------------------------------

class SeoController
{
    public function __construct(private readonly SEOJuice $client) {}

    public function intelligence(string $domain): JsonResponse
    {
        $summary = $this->client->intelligence($domain)->summary(
            period: Period::ThirtyDays,
            includeTrends: true,
        );
        $aiso = $this->client->aiso($domain)->get(Period::ThirtyDays);

        return new JsonResponse([
            'domain' => $domain,
            'seo_score' => $summary->seoScore,
            'aiso_score' => $aiso->aisoScore,
            'total_pages' => $summary->totalPages,
            'total_mentions' => $aiso->totalMentions,
        ]);
    }
}
