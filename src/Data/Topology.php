<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class Topology
{
    public readonly int $totalPages;
    public readonly int $totalInternalLinks;
    public readonly int $orphanPagesCount;
    /** @var array<int, mixed> */
    public readonly array $orphanPages;
    /** @var array<string, mixed> */
    public readonly array $linkDepthDistribution;
    public readonly float $avgLinksPerPage;
    /** @var array<int, mixed> */
    public readonly array $mostLinkedPages;

    /**
     * @param array<int, mixed> $orphanPages
     * @param array<string, mixed> $linkDepthDistribution
     * @param array<int, mixed> $mostLinkedPages
     */
    public function __construct(
        int $totalPages,
        int $totalInternalLinks,
        int $orphanPagesCount,
        array $orphanPages,
        array $linkDepthDistribution,
        float $avgLinksPerPage,
        array $mostLinkedPages,
    ) {
        $this->totalPages = $totalPages;
        $this->totalInternalLinks = $totalInternalLinks;
        $this->orphanPagesCount = $orphanPagesCount;
        $this->orphanPages = $orphanPages;
        $this->linkDepthDistribution = $linkDepthDistribution;
        $this->avgLinksPerPage = $avgLinksPerPage;
        $this->mostLinkedPages = $mostLinkedPages;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            totalPages: (int) ($data['total_pages'] ?? 0),
            totalInternalLinks: (int) ($data['total_internal_links'] ?? 0),
            orphanPagesCount: (int) ($data['orphan_pages_count'] ?? 0),
            orphanPages: $data['orphan_pages'] ?? [],
            linkDepthDistribution: $data['link_depth_distribution'] ?? [],
            avgLinksPerPage: (float) ($data['avg_links_per_page'] ?? 0),
            mostLinkedPages: $data['most_linked_pages'] ?? [],
        );
    }
}
