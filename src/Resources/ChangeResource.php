<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\ChangeRecord;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\HttpClient;

final class ChangeResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function list(
        ?string $status = null,
        ?string $changeType = null,
        ?string $riskLevel = null,
        int $page = 1,
        int $pageSize = 10,
    ): PaginatedResult {
        $data = $this->http->get("websites/{$this->domain}/changes/", [
            'status' => $status,
            'change_type' => $changeType,
            'risk_level' => $riskLevel,
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => ChangeRecord::fromArray($item));
    }
}
