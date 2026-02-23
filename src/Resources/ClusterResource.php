<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\ClusterDetail;
use SEOJuice\Data\ClusterSummary;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\HttpClient;

final class ClusterResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function list(int $page = 1, int $pageSize = 10): PaginatedResult
    {
        $data = $this->http->get("websites/{$this->domain}/clusters/", [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => ClusterSummary::fromArray($item));
    }

    public function get(int $clusterId): ClusterDetail
    {
        $data = $this->http->get("websites/{$this->domain}/clusters/{$clusterId}/");

        return ClusterDetail::fromArray($data);
    }
}
