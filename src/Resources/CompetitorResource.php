<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\Competitor;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\HttpClient;

final class CompetitorResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function list(
        bool $includeTrends = false,
        int $page = 1,
        int $pageSize = 10,
    ): PaginatedResult {
        $data = $this->http->get("websites/{$this->domain}/competitors/", [
            'include_trends' => $includeTrends ? 'true' : null,
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => Competitor::fromArray($item));
    }
}
