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

    // suggestions() is fail-open — it returns [] on any network/parse error.
    $data = $client->smart()->suggestions($url);

    $metaTags = [
        'description' => $data['meta_description'] ?? '',
        'keywords' => $data['meta_keywords'] ?? '',
        'og:title' => $data['og_title'] ?? '',
        'og:description' => $data['og_description'] ?? '',
        'og:image' => $data['og_image'] ?? '',
    ];

    foreach ($metaTags as $name => $content) {
        if ($content === '' || $content === null) {
            continue;
        }

        $attachments['#attached']['html_head'][] = [
            [
                '#tag' => 'meta',
                '#attributes' => str_starts_with($name, 'og:')
                    ? ['property' => $name, 'content' => $content]
                    : ['name' => $name, 'content' => $content],
            ],
            'seojuice_' . str_replace(':', '_', $name),
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
