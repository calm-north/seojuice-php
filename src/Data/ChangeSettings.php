<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class ChangeSettings
{
    public readonly string $internalLinksMode;
    public readonly string $metaTagsMode;
    public readonly string $ogTagsMode;
    public readonly string $titleTagsMode;
    public readonly string $structuredDataMode;
    public readonly string $imageAltMode;
    public readonly string $accessibilityMode;
    public readonly string $localSeoMode;
    public readonly string $gbpReviewReplyMode;
    public readonly int $maxChangesPerPagePerDay;
    public readonly int $maxChangesPerDay;
    public readonly string $excludePaths;

    public function __construct(
        string $internalLinksMode,
        string $metaTagsMode,
        string $ogTagsMode,
        string $titleTagsMode,
        string $structuredDataMode,
        string $imageAltMode,
        string $accessibilityMode,
        string $localSeoMode,
        string $gbpReviewReplyMode,
        int $maxChangesPerPagePerDay,
        int $maxChangesPerDay,
        string $excludePaths,
    ) {
        $this->internalLinksMode = $internalLinksMode;
        $this->metaTagsMode = $metaTagsMode;
        $this->ogTagsMode = $ogTagsMode;
        $this->titleTagsMode = $titleTagsMode;
        $this->structuredDataMode = $structuredDataMode;
        $this->imageAltMode = $imageAltMode;
        $this->accessibilityMode = $accessibilityMode;
        $this->localSeoMode = $localSeoMode;
        $this->gbpReviewReplyMode = $gbpReviewReplyMode;
        $this->maxChangesPerPagePerDay = $maxChangesPerPagePerDay;
        $this->maxChangesPerDay = $maxChangesPerDay;
        $this->excludePaths = $excludePaths;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            internalLinksMode: (string) ($data['internal_links_mode'] ?? 'off'),
            metaTagsMode: (string) ($data['meta_tags_mode'] ?? 'off'),
            ogTagsMode: (string) ($data['og_tags_mode'] ?? 'off'),
            titleTagsMode: (string) ($data['title_tags_mode'] ?? 'off'),
            structuredDataMode: (string) ($data['structured_data_mode'] ?? 'off'),
            imageAltMode: (string) ($data['image_alt_mode'] ?? 'off'),
            accessibilityMode: (string) ($data['accessibility_mode'] ?? 'off'),
            localSeoMode: (string) ($data['local_seo_mode'] ?? 'off'),
            gbpReviewReplyMode: (string) ($data['gbp_review_reply_mode'] ?? 'off'),
            maxChangesPerPagePerDay: (int) ($data['max_changes_per_page_per_day'] ?? 3),
            maxChangesPerDay: (int) ($data['max_changes_per_day'] ?? 25),
            excludePaths: (string) ($data['exclude_paths'] ?? ''),
        );
    }
}
