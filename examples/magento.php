<?php

/**
 * Magento 2 integration patterns for SEOJuice.
 *
 * Demonstrates an observer for SEO tag injection and a ViewModel
 * for rendering intelligence data in .phtml templates.
 *
 * Requirements:
 *     composer require seojuice/seojuice
 *
 * DI configuration (etc/di.xml):
 *     <type name="SEOJuice\SEOJuice">
 *         <arguments>
 *             <argument name="apiKey" xsi:type="string">YOUR_API_KEY</argument>
 *         </arguments>
 *     </type>
 *
 * Events (etc/frontend/events.xml):
 *     <event name="layout_generate_blocks_after">
 *         <observer name="seojuice_seo_injection"
 *                   instance="Vendor\SeoJuice\Observer\SeoInjectionObserver"/>
 *     </event>
 *
 * Layout (view/frontend/layout/default.xml):
 *     <block class="Magento\Framework\View\Element\Template"
 *            name="seojuice.data"
 *            template="Vendor_SeoJuice::seo_data.phtml">
 *         <arguments>
 *             <argument name="view_model" xsi:type="object">
 *                 Vendor\SeoJuice\ViewModel\SeoDataViewModel
 *             </argument>
 *         </arguments>
 *     </block>
 */

declare(strict_types=1);

namespace Vendor\SeoJuice\Observer;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use SEOJuice\Data\IntelligenceSummary;
use SEOJuice\Enums\Period;
use SEOJuice\Injection\SeoInjector;
use SEOJuice\SEOJuice;

// ---------------------------------------------------------------------------
// 1. Observer — inject SEO tags into the response body
// ---------------------------------------------------------------------------

class SeoInjectionObserver implements ObserverInterface
{
    public function __construct(
        private readonly SEOJuice $client,
        private readonly ResponseInterface $response,
        private readonly \Magento\Framework\UrlInterface $urlBuilder,
    ) {}

    public function execute(Observer $observer): void
    {
        $body = $this->response->getBody();
        if (!is_string($body) || !str_contains($body, '</head>')) {
            return;
        }

        $url = $this->urlBuilder->getCurrentUrl();

        try {
            $suggestions = $this->client->smart()->suggestions($url);
        } catch (\Exception) {
            return;
        }

        if ($suggestions->isEmpty()) {
            return;
        }

        $injector = new SeoInjector();
        $this->response->setBody($injector->inject($body, $suggestions));
    }
}

// ---------------------------------------------------------------------------
// 2. ViewModel — provide SEO data to .phtml templates
// ---------------------------------------------------------------------------

class SeoDataViewModel implements ArgumentInterface
{
    public function __construct(private readonly SEOJuice $client) {}

    public function getIntelligenceSummary(string $domain): IntelligenceSummary
    {
        return $this->client->intelligence($domain)->summary(
            period: Period::ThirtyDays,
            includeTrends: true,
        );
    }

    public function getAisoScore(string $domain): float
    {
        return $this->client->aiso($domain)->get(Period::ThirtyDays)->aisoScore;
    }
}

// Template usage (seo_data.phtml):
//
// /** @var \Vendor\SeoJuice\ViewModel\SeoDataViewModel $viewModel */
// $viewModel = $block->getData('view_model');
// $summary = $viewModel->getIntelligenceSummary('example.com');
//
// <div class="seojuice-widget">
//     <p>SEO Score: <?= $summary->seoScore ?></p>
//     <p>Total Pages: <?= $summary->totalPages ?></p>
//     <p>AISO Score: <?= $viewModel->getAisoScore('example.com') ?></p>
// </div>
