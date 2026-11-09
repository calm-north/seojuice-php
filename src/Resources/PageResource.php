<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\MetricsHistory;
use SEOJuice\Data\Page;
use SEOJuice\Data\PageKeyword;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\Data\SearchStats;
use SEOJuice\HttpClient;

final class PageResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function list(int $page = 1, int $pageSize = 10, ?string $url = null): PaginatedResult
    {
        $query = ['page' => $page, 'page_size' => $pageSize];
        if ($url !== null) {
            $query['url'] = $url;
        }
        $data = $this->http->get("websites/{$this->domain}/pages/", $query);

        return PaginatedResult::fromArray($data, fn (array $item) => Page::fromArray($item));
    }

    public function get(string $pageId): Page
    {
        $data = $this->http->get("websites/{$this->domain}/pages/{$pageId}/");

        return Page::fromArray($data);
    }

    public function keywords(string $pageId, int $page = 1, int $pageSize = 10): PaginatedResult
    {
        $data = $this->http->get("websites/{$this->domain}/pages/{$pageId}/keywords/", [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => PageKeyword::fromArray($item));
    }

    /**
     * @param string|null $period 7d, 30d or 90d; the server defaults to 30d (search stats) and 90d (metrics history)
     */
    public function searchStats(string $pageId, int $page = 1, int $pageSize = 10, ?string $period = null): PaginatedResult
    {
        $query = ['page' => $page, 'page_size' => $pageSize];
        if ($period !== null) {
            $query['period'] = $period;
        }
        $data = $this->http->get("websites/{$this->domain}/pages/{$pageId}/search-stats/", $query);

        return PaginatedResult::fromArray($data, fn (array $item) => SearchStats::fromArray($item));
    }

    /**
     * @param string|null $period 7d, 30d or 90d; the server defaults to 30d (search stats) and 90d (metrics history)
     */
    public function metricsHistory(string $pageId, int $page = 1, int $pageSize = 10, ?string $period = null): PaginatedResult
    {
        $query = ['page' => $page, 'page_size' => $pageSize];
        if ($period !== null) {
            $query['period'] = $period;
        }
        $data = $this->http->get("websites/{$this->domain}/pages/{$pageId}/metrics-history/", $query);

        return PaginatedResult::fromArray($data, fn (array $item) => MetricsHistory::fromArray($item));
    }

    /**
     * @return array<string, mixed>
     */
    public function content(string $pageId): array
    {
        return $this->http->get("websites/{$this->domain}/pages/{$pageId}/content/");
    }

    /**
     * @return array<string, mixed>
     */
    public function contentQuality(string $pageId, bool $fullAudit = false): array
    {
        return $this->http->get("websites/{$this->domain}/pages/{$pageId}/content-quality/", [
            'full_audit' => $fullAudit ? 'true' : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function geoReadiness(string $pageId, bool $fullAudit = false): array
    {
        return $this->http->get("websites/{$this->domain}/pages/{$pageId}/geo-readiness/", [
            'full_audit' => $fullAudit ? 'true' : null,
        ]);
    }
}
