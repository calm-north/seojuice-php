<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\ContentDecayAlert;
use SEOJuice\Data\ContentGap;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\HttpClient;

final class ContentResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function listGaps(
        ?string $category = null,
        ?string $intent = null,
        int $page = 1,
        int $pageSize = 10,
    ): PaginatedResult {
        $data = $this->http->get("websites/{$this->domain}/content/gaps/", [
            'category' => $category,
            'intent' => $intent,
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => ContentGap::fromArray($item));
    }

    public function listDecayAlerts(
        ?bool $isActive = null,
        ?string $severity = null,
        ?string $decayType = null,
        int $page = 1,
        int $pageSize = 10,
    ): PaginatedResult {
        $data = $this->http->get("websites/{$this->domain}/content/decay-alerts/", [
            'is_active' => $isActive !== null ? ($isActive ? 'true' : 'false') : null,
            'severity' => $severity,
            'decay_type' => $decayType,
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => ContentDecayAlert::fromArray($item));
    }
}
