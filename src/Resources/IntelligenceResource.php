<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\IntelligenceSummary;
use SEOJuice\Data\PageSpeed;
use SEOJuice\Data\Topology;
use SEOJuice\Enums\Period;
use SEOJuice\HttpClient;

final class IntelligenceResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function summary(
        Period $period = Period::ThirtyDays,
        bool $includeHistory = false,
        bool $includeTrends = false,
    ): IntelligenceSummary {
        $data = $this->http->get("websites/{$this->domain}/intelligence/summary/", [
            'period' => $period->value,
            'include_history' => $includeHistory ? 'true' : null,
            'include_trends' => $includeTrends ? 'true' : null,
        ]);

        return IntelligenceSummary::fromArray($data);
    }

    public function topology(): Topology
    {
        $data = $this->http->get("websites/{$this->domain}/intelligence/topology/");

        return Topology::fromArray($data);
    }

    public function pageSpeed(string $url): PageSpeed
    {
        $data = $this->http->get("websites/{$this->domain}/intelligence/page-speed/", [
            'url' => $url,
        ]);

        return PageSpeed::fromArray($data);
    }
}
