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

    public function list(int $page = 1, int $pageSize = 10): PaginatedResult
    {
        $data = $this->http->get("websites/{$this->domain}/pages/", [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

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

    public function searchStats(string $pageId, int $page = 1, int $pageSize = 10): PaginatedResult
    {
        $data = $this->http->get("websites/{$this->domain}/pages/{$pageId}/search-stats/", [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => SearchStats::fromArray($item));
    }

    public function metricsHistory(string $pageId, int $page = 1, int $pageSize = 10): PaginatedResult
    {
        $data = $this->http->get("websites/{$this->domain}/pages/{$pageId}/metrics-history/", [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => MetricsHistory::fromArray($item));
    }
}
