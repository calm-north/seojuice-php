<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\Link;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\HttpClient;

final class LinkResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function list(int $page = 1, int $pageSize = 10): PaginatedResult
    {
        $data = $this->http->get("websites/{$this->domain}/links/", [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => Link::fromArray($item));
    }
}
