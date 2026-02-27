<?php

declare(strict_types=1);

namespace SEOJuice;

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
use SEOJuice\Resources\SimilarResource;
use SEOJuice\Resources\WebsiteResource;

final class SEOJuice
{
    private readonly HttpClient $http;
    private readonly Config $config;

    public function __construct(
        string $apiKey,
        ?Config $config = null,
    ) {
        $this->config = $config ?? new Config();
        $this->http = new HttpClient($apiKey, $this->config);
    }

    public function websites(): WebsiteResource
    {
        return new WebsiteResource($this->http);
    }

    public function pages(string $domain): PageResource
    {
        return new PageResource($this->http, $domain);
    }

    public function links(string $domain): LinkResource
    {
        return new LinkResource($this->http, $domain);
    }

    public function intelligence(string $domain): IntelligenceResource
    {
        return new IntelligenceResource($this->http, $domain);
    }

    public function clusters(string $domain): ClusterResource
    {
        return new ClusterResource($this->http, $domain);
    }

    public function content(string $domain): ContentResource
    {
        return new ContentResource($this->http, $domain);
    }

    public function competitors(string $domain): CompetitorResource
    {
        return new CompetitorResource($this->http, $domain);
    }

    public function aiso(string $domain): AisoResource
    {
        return new AisoResource($this->http, $domain);
    }

    public function analysis(string $domain): AnalysisResource
    {
        return new AnalysisResource($this->http, $domain);
    }

    public function reports(string $domain): ReportResource
    {
        return new ReportResource($this->http, $domain);
    }

    public function keywords(string $domain): KeywordResource
    {
        return new KeywordResource($this->http, $domain);
    }

    public function backlinks(string $domain): BacklinkResource
    {
        return new BacklinkResource($this->http, $domain);
    }

    public function accessibility(string $domain): AccessibilityResource
    {
        return new AccessibilityResource($this->http, $domain);
    }

    public function changes(string $domain): ChangeResource
    {
        return new ChangeResource($this->http, $domain);
    }

    public function gbp(string $domain): GbpResource
    {
        return new GbpResource($this->http, $domain);
    }

    public function similar(string $domain): SimilarResource
    {
        return new SimilarResource($this->http, $domain);
    }

    public function smart(): SmartClient
    {
        return new SmartClient($this->config);
    }
}
