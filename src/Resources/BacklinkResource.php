<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\Backlink;
use SEOJuice\Data\BacklinkDomain;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\HttpClient;

final class BacklinkResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function list(
        ?string $status = null,
        ?bool $dofollow = null,
        int $page = 1,
        int $pageSize = 10,
    ): PaginatedResult {
        $data = $this->http->get("websites/{$this->domain}/backlinks/", [
            'status' => $status,
            'dofollow' => $dofollow !== null ? ($dofollow ? 'true' : 'false') : null,
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => Backlink::fromArray($item));
    }

    public function listDomains(int $page = 1, int $pageSize = 10): PaginatedResult
    {
        $data = $this->http->get("websites/{$this->domain}/backlinks/domains/", [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => BacklinkDomain::fromArray($item));
    }
}
