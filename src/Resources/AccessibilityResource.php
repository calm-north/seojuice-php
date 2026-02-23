<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\AccessibilityIssue;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\HttpClient;

final class AccessibilityResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function list(
        ?string $severity = null,
        ?string $category = null,
        ?bool $autoFixable = null,
        int $page = 1,
        int $pageSize = 10,
    ): PaginatedResult {
        $data = $this->http->get("websites/{$this->domain}/accessibility/", [
            'severity' => $severity,
            'category' => $category,
            'auto_fixable' => $autoFixable !== null ? ($autoFixable ? 'true' : 'false') : null,
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => AccessibilityIssue::fromArray($item));
    }
}
