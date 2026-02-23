<?php

/**
 * Drupal 10 module integration patterns for SEOJuice.
 *
 * Demonstrates hook-based meta tag injection and a custom block plugin
 * for displaying SEO intelligence data.
 *
 * Requirements:
 *     composer require seojuice/seojuice
 *
 * Module info (seojuice.info.yml):
 *     name: SEOJuice
 *     type: module
 *     description: SEO intelligence integration
 *     core_version_requirement: ^10
 *     package: SEO
 *
 * Services (seojuice.services.yml):
 *     seojuice.client:
 *         class: SEOJuice\SEOJuice
 *         arguments:
 *             - '%seojuice.api_key%'
 *
 * Parameters (seojuice.services.yml or settings.php):
 *     parameters:
 *         seojuice.api_key: '%env(SEOJUICE_API_KEY)%'
 */

declare(strict_types=1);

use Drupal\Core\Block\BlockBase;
use SEOJuice\Enums\Period;
use SEOJuice\Injection\SeoInjector;
use SEOJuice\SEOJuice;

// ---------------------------------------------------------------------------
// 1. Hook — inject meta tags via page_attachments_alter
// ---------------------------------------------------------------------------

/**
 * Implements hook_page_attachments_alter().
 *
 * @param array<string, mixed> $attachments
 */
function seojuice_page_attachments_alter(array &$attachments): void
{
    /** @var SEOJuice $client */
    $client = \Drupal::service('seojuice.client');
    $url = \Drupal::request()->getUri();

    try {
        $suggestions = $client->smart()->suggestions($url);
    } catch (\Exception) {
        return; // fail-open
    }

    if ($suggestions->isEmpty()) {
        return;
    }

    foreach ($suggestions->metaTags as $name => $content) {
        if ($content === '' || $content === null) {
            continue;
        }

        $attachments['#attached']['html_head'][] = [
            [
                '#tag' => 'meta',
                '#attributes' => ['name' => $name, 'content' => $content],
            ],
            "seojuice_{$name}",
        ];
    }
}

// ---------------------------------------------------------------------------
// 2. Block Plugin — display SEO intelligence data
// ---------------------------------------------------------------------------

/**
 * @Block(
 *   id = "seojuice_intelligence",
 *   admin_label = @Translation("SEOJuice Intelligence"),
 *   category = @Translation("SEO"),
 * )
 */
class SeoJuiceBlock extends BlockBase
{
    /** @return array<string, mixed> */
    public function build(): array
    {
        /** @var SEOJuice $client */
        $client = \Drupal::service('seojuice.client');
        $domain = \Drupal::request()->getHost();

        $summary = $client->intelligence($domain)->summary(Period::ThirtyDays);

        return [
            '#theme' => 'seojuice_intelligence',
            '#seo_score' => $summary->seoScore,
            '#aiso_score' => $summary->aisoScore,
            '#total_pages' => $summary->totalPages,
            '#orphan_pages' => $summary->orphanPages,
            '#cache' => ['max-age' => 3600],
        ];
    }
}
