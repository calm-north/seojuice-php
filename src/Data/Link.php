<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class Link
{
    public readonly string $pageFrom;
    public readonly string $pageTo;
    public readonly ?string $keyword;
    public readonly ?int $impressions;
    public readonly ?string $createdAt;

    public function __construct(
        string $pageFrom,
        string $pageTo,
        ?string $keyword,
        ?int $impressions,
        ?string $createdAt,
    ) {
        $this->pageFrom = $pageFrom;
        $this->pageTo = $pageTo;
        $this->keyword = $keyword;
        $this->impressions = $impressions;
        $this->createdAt = $createdAt;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            pageFrom: (string) $data['page_from'],
            pageTo: (string) $data['page_to'],
            keyword: $data['keyword'] ?? null,
            impressions: isset($data['impressions']) ? (int) $data['impressions'] : null,
            createdAt: $data['created_at'] ?? null,
        );
    }
}
