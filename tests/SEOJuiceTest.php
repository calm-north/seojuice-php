<?php

declare(strict_types=1);

namespace SEOJuice\Tests;

use PHPUnit\Framework\TestCase;
use SEOJuice\Config;
use SEOJuice\Injection\SmartClient;
use SEOJuice\Resources\AccessibilityResource;
use SEOJuice\Resources\AisoResource;
use SEOJuice\Resources\AnalysisResource;
use SEOJuice\Resources\BacklinkResource;
use SEOJuice\Resources\ChangeResource;
use SEOJuice\Resources\ClusterResource;
use SEOJuice\Resources\CompetitorResource;
use SEOJuice\Resources\ContentResource;
use SEOJuice\Resources\GbpResource;
use SEOJuice\Resources\IntelligenceResource;
use SEOJuice\Resources\KeywordResource;
use SEOJuice\Resources\LinkResource;
use SEOJuice\Resources\PageResource;
use SEOJuice\Resources\ReportResource;
use SEOJuice\Resources\WebsiteResource;
use SEOJuice\SEOJuice;

final class SEOJuiceTest extends TestCase
{
    private SEOJuice $sdk;

    protected function setUp(): void
    {
        $this->sdk = new SEOJuice('test-api-key');
    }

    public function testConstructorCreatesInstanceWithApiKey(): void
    {
        $sdk = new SEOJuice('my-api-key');
        $this->assertInstanceOf(SEOJuice::class, $sdk);
    }

    public function testConstructorAcceptsCustomConfig(): void
    {
        $config = new Config(
            baseUrl: 'https://custom.api.com/v2',
            timeout: 60,
        );
        $sdk = new SEOJuice('my-api-key', $config);
        $this->assertInstanceOf(SEOJuice::class, $sdk);
    }

    public function testWebsitesReturnsWebsiteResource(): void
    {
        $this->assertInstanceOf(WebsiteResource::class, $this->sdk->websites());
    }

    public function testPagesReturnsPageResource(): void
    {
        $this->assertInstanceOf(PageResource::class, $this->sdk->pages('example.com'));
    }

    public function testLinksReturnsLinkResource(): void
    {
        $this->assertInstanceOf(LinkResource::class, $this->sdk->links('example.com'));
    }

    public function testIntelligenceReturnsIntelligenceResource(): void
    {
        $this->assertInstanceOf(IntelligenceResource::class, $this->sdk->intelligence('example.com'));
    }

    public function testClustersReturnsClusterResource(): void
    {
        $this->assertInstanceOf(ClusterResource::class, $this->sdk->clusters('example.com'));
    }

    public function testContentReturnsContentResource(): void
    {
        $this->assertInstanceOf(ContentResource::class, $this->sdk->content('example.com'));
    }

    public function testCompetitorsReturnsCompetitorResource(): void
    {
        $this->assertInstanceOf(CompetitorResource::class, $this->sdk->competitors('example.com'));
    }

    public function testAisoReturnsAisoResource(): void
    {
        $this->assertInstanceOf(AisoResource::class, $this->sdk->aiso('example.com'));
    }

    public function testAnalysisReturnsAnalysisResource(): void
    {
        $this->assertInstanceOf(AnalysisResource::class, $this->sdk->analysis('example.com'));
    }

    public function testReportsReturnsReportResource(): void
    {
        $this->assertInstanceOf(ReportResource::class, $this->sdk->reports('example.com'));
    }

    public function testKeywordsReturnsKeywordResource(): void
    {
        $this->assertInstanceOf(KeywordResource::class, $this->sdk->keywords('example.com'));
    }

    public function testBacklinksReturnsBacklinkResource(): void
    {
        $this->assertInstanceOf(BacklinkResource::class, $this->sdk->backlinks('example.com'));
    }

    public function testAccessibilityReturnsAccessibilityResource(): void
    {
        $this->assertInstanceOf(AccessibilityResource::class, $this->sdk->accessibility('example.com'));
    }

    public function testChangesReturnsChangeResource(): void
    {
        $this->assertInstanceOf(ChangeResource::class, $this->sdk->changes('example.com'));
    }

    public function testGbpReturnsGbpResource(): void
    {
        $this->assertInstanceOf(GbpResource::class, $this->sdk->gbp('example.com'));
    }

    public function testSmartReturnsSmartClient(): void
    {
        $this->assertInstanceOf(SmartClient::class, $this->sdk->smart());
    }

    public function testResourceAccessorsReturnNewInstancesEachCall(): void
    {
        $websites1 = $this->sdk->websites();
        $websites2 = $this->sdk->websites();

        $this->assertNotSame($websites1, $websites2);
    }

    public function testDomainScopedResourcesAcceptDifferentDomains(): void
    {
        $pages1 = $this->sdk->pages('example.com');
        $pages2 = $this->sdk->pages('other.com');

        $this->assertInstanceOf(PageResource::class, $pages1);
        $this->assertInstanceOf(PageResource::class, $pages2);
        $this->assertNotSame($pages1, $pages2);
    }
}
