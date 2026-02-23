<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\Keyword;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\HttpClient;

final class KeywordResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function list(
        ?string $category = null,
        int $page = 1,
        int $pageSize = 10,
    ): PaginatedResult {
        $data = $this->http->get("websites/{$this->domain}/keywords/", [
            'category' => $category,
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => Keyword::fromArray($item));
    }
}
