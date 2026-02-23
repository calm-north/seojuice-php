<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class Backlink
{
    public readonly int $id;
    public readonly string $sourceUrl;
    public readonly string $targetUrl;
    public readonly ?string $anchorText;
    public readonly ?string $status;
    public readonly ?string $linkType;
    public readonly bool $dofollow;
    public readonly bool $nofollow;
    public readonly bool $isNew;
    public readonly bool $isLost;
    public readonly ?int $pageFromRank;
    public readonly ?string $firstDiscoveredAt;
    public readonly ?string $lastCrawledAt;

    public function __construct(
        int $id,
        string $sourceUrl,
        string $targetUrl,
        ?string $anchorText,
        ?string $status,
        ?string $linkType,
        bool $dofollow,
        bool $nofollow,
        bool $isNew,
        bool $isLost,
        ?int $pageFromRank,
        ?string $firstDiscoveredAt,
        ?string $lastCrawledAt,
    ) {
        $this->id = $id;
        $this->sourceUrl = $sourceUrl;
        $this->targetUrl = $targetUrl;
        $this->anchorText = $anchorText;
        $this->status = $status;
        $this->linkType = $linkType;
        $this->dofollow = $dofollow;
        $this->nofollow = $nofollow;
        $this->isNew = $isNew;
        $this->isLost = $isLost;
        $this->pageFromRank = $pageFromRank;
        $this->firstDiscoveredAt = $firstDiscoveredAt;
        $this->lastCrawledAt = $lastCrawledAt;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            sourceUrl: (string) $data['source_url'],
            targetUrl: (string) $data['target_url'],
            anchorText: $data['anchor_text'] ?? null,
            status: $data['status'] ?? null,
            linkType: $data['link_type'] ?? null,
            dofollow: (bool) ($data['dofollow'] ?? false),
            nofollow: (bool) ($data['nofollow'] ?? false),
            isNew: (bool) ($data['is_new'] ?? false),
            isLost: (bool) ($data['is_lost'] ?? false),
            pageFromRank: isset($data['page_from_rank']) ? (int) $data['page_from_rank'] : null,
            firstDiscoveredAt: $data['first_discovered_at'] ?? null,
            lastCrawledAt: $data['last_crawled_at'] ?? null,
        );
    }
}
