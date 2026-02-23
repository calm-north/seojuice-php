<?php

declare(strict_types=1);

namespace SEOJuice\Injection;

final class Suggestions
{
    /** @var array<int, mixed> */
    public readonly array $links;
    /** @var array<int, mixed> */
    public readonly array $images;
    /** @var array<string, mixed> */
    public readonly array $metaTags;
    /** @var array<int, mixed> */
    public readonly array $structuredData;
    /** @var array<int, mixed> */
    public readonly array $accessibilityFixes;
    /** @var array<string, mixed> */
    public readonly array $ogTags;

    /**
     * @param array<int, mixed> $links
     * @param array<int, mixed> $images
     * @param array<string, mixed> $metaTags
     * @param array<int, mixed> $structuredData
     * @param array<int, mixed> $accessibilityFixes
     * @param array<string, mixed> $ogTags
     */
    public function __construct(
        array $links,
        array $images,
        array $metaTags,
        array $structuredData,
        array $accessibilityFixes,
        array $ogTags,
    ) {
        $this->links = $links;
        $this->images = $images;
        $this->metaTags = $metaTags;
        $this->structuredData = $structuredData;
        $this->accessibilityFixes = $accessibilityFixes;
        $this->ogTags = $ogTags;
    }

    public function isEmpty(): bool
    {
        return $this->links === []
            && $this->images === []
            && $this->metaTags === []
            && $this->structuredData === []
            && $this->accessibilityFixes === []
            && $this->ogTags === [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            links: $data['links'] ?? [],
            images: $data['images'] ?? [],
            metaTags: $data['meta_tags'] ?? [],
            structuredData: $data['structured_data'] ?? [],
            accessibilityFixes: $data['accessibility_fixes'] ?? [],
            ogTags: $data['og_tags'] ?? [],
        );
    }
}
