<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\SimilarPagesResult;
use SEOJuice\HttpClient;

final class SimilarResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function find(string $url, int $limit = 10): SimilarPagesResult
    {
        $data = $this->http->get("websites/{$this->domain}/similar/", [
            'url' => $url,
            'limit' => $limit,
        ]);

        return SimilarPagesResult::fromArray($data);
    }
}
