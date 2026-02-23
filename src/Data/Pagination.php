<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class Pagination
{
    public readonly int $page;
    public readonly int $pageSize;
    public readonly int $totalCount;
    public readonly int $totalPages;

    public function __construct(
        int $page,
        int $pageSize,
        int $totalCount,
        int $totalPages,
    ) {
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->totalCount = $totalCount;
        $this->totalPages = $totalPages;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            page: (int) ($data['page'] ?? 1),
            pageSize: (int) ($data['page_size'] ?? 10),
            totalCount: (int) ($data['total_count'] ?? 0),
            totalPages: (int) ($data['total_pages'] ?? 0),
        );
    }
}
