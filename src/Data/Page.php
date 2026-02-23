<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class Page
{
    public readonly int $id;
    public readonly string $url;
    public readonly ?string $title;
    public readonly ?string $pageType;
    public readonly ?float $seoScore;
    public readonly ?float $accessibilityScore;
    public readonly ?float $onpageScore;
    public readonly ?float $conversionScore;
    public readonly ?string $metaDescription;
    public readonly ?string $languageCode;
    public readonly ?string $ogTitle;
    public readonly ?string $ogDescription;
    public readonly ?string $ogImage;
    /** @var array<string, mixed> */
    public readonly array $readability;
    /** @var array<string, mixed> */
    public readonly array $structuredData;
    /** @var array<int, Link> */
    public readonly array $links;
    public readonly ?string $createdAt;
    public readonly ?string $lastProcessedAt;

    /**
     * @param array<string, mixed> $readability
     * @param array<string, mixed> $structuredData
     * @param array<int, Link> $links
     */
    public function __construct(
        int $id,
        string $url,
        ?string $title,
        ?string $pageType,
        ?float $seoScore,
        ?float $accessibilityScore,
        ?float $onpageScore,
        ?float $conversionScore,
        ?string $metaDescription,
        ?string $languageCode,
        ?string $ogTitle,
        ?string $ogDescription,
        ?string $ogImage,
        array $readability,
        array $structuredData,
        array $links,
        ?string $createdAt,
        ?string $lastProcessedAt,
    ) {
        $this->id = $id;
        $this->url = $url;
        $this->title = $title;
        $this->pageType = $pageType;
        $this->seoScore = $seoScore;
        $this->accessibilityScore = $accessibilityScore;
        $this->onpageScore = $onpageScore;
        $this->conversionScore = $conversionScore;
        $this->metaDescription = $metaDescription;
        $this->languageCode = $languageCode;
        $this->ogTitle = $ogTitle;
        $this->ogDescription = $ogDescription;
        $this->ogImage = $ogImage;
        $this->readability = $readability;
        $this->structuredData = $structuredData;
        $this->links = $links;
        $this->createdAt = $createdAt;
        $this->lastProcessedAt = $lastProcessedAt;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $linksData = $data['links'] ?? [];
        $links = array_map(
            fn (array $link) => Link::fromArray($link),
            $linksData,
        );

        return new self(
            id: (int) $data['id'],
            url: (string) $data['url'],
            title: $data['title'] ?? null,
            pageType: $data['page_type'] ?? null,
            seoScore: isset($data['seo_score']) ? (float) $data['seo_score'] : null,
            accessibilityScore: isset($data['accessibility_score']) ? (float) $data['accessibility_score'] : null,
            onpageScore: isset($data['onpage_score']) ? (float) $data['onpage_score'] : null,
            conversionScore: isset($data['conversion_score']) ? (float) $data['conversion_score'] : null,
            metaDescription: $data['meta_description'] ?? null,
            languageCode: $data['language_code'] ?? null,
            ogTitle: $data['og_title'] ?? null,
            ogDescription: $data['og_description'] ?? null,
            ogImage: $data['og_image'] ?? null,
            readability: $data['readability'] ?? [],
            structuredData: $data['structured_data'] ?? [],
            links: $links,
            createdAt: $data['created_at'] ?? null,
            lastProcessedAt: $data['last_processed_at'] ?? null,
        );
    }
}
