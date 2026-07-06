<?php

declare(strict_types=1);

namespace SEOJuice\Injection;

final class Suggestions
{
    /**
     * @param array<int, array<string, mixed>> $suggestions
     * @param array<int, array<string, mixed>> $images
     * @param array<int, array<string, mixed>> $diffs
     * @param array<int, array<string, mixed>> $brokenLinkFixes
     * @param array<int, mixed> $errors
     */
    public function __construct(
        public readonly array $suggestions = [],
        public readonly array $images = [],
        public readonly array $diffs = [],
        public readonly array $brokenLinkFixes = [],
        public readonly string $title = '',
        public readonly string $metaDescription = '',
        public readonly string $metaKeywords = '',
        public readonly string $ogTitle = '',
        public readonly string $ogDescription = '',
        public readonly string $ogUrl = '',
        public readonly string $ogImage = '',
        public readonly string $structuredData = '',
        public readonly string $h1 = '',
        public readonly bool $isAsian = false,
        public readonly string $customLinkClass = '',
        public readonly bool $insertIntoContentOnly = false,
        public readonly array $errors = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            suggestions: self::arrayOrEmpty($data['suggestions'] ?? null),
            images: self::arrayOrEmpty($data['images'] ?? null),
            diffs: self::arrayOrEmpty($data['diffs'] ?? null),
            brokenLinkFixes: self::arrayOrEmpty($data['broken_link_fixes'] ?? null),
            title: (string) ($data['title'] ?? ''),
            metaDescription: (string) ($data['meta_description'] ?? ''),
            metaKeywords: (string) ($data['meta_keywords'] ?? ''),
            ogTitle: (string) ($data['og_title'] ?? ''),
            ogDescription: (string) ($data['og_description'] ?? ''),
            ogUrl: (string) ($data['og_url'] ?? ''),
            ogImage: (string) ($data['og_image'] ?? ''),
            structuredData: (string) ($data['structured_data'] ?? ''),
            h1: (string) ($data['h1'] ?? ''),
            isAsian: (bool) ($data['isAsian'] ?? false),
            customLinkClass: (string) ($data['custom_link_class'] ?? ''),
            insertIntoContentOnly: (bool) ($data['insert_into_content_only'] ?? false),
            errors: self::arrayOrEmpty($data['errors'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'suggestions' => $this->suggestions,
            'images' => $this->images,
            'diffs' => $this->diffs,
            'broken_link_fixes' => $this->brokenLinkFixes,
            'title' => $this->title,
            'meta_description' => $this->metaDescription,
            'meta_keywords' => $this->metaKeywords,
            'og_title' => $this->ogTitle,
            'og_description' => $this->ogDescription,
            'og_url' => $this->ogUrl,
            'og_image' => $this->ogImage,
            'structured_data' => $this->structuredData,
            'h1' => $this->h1,
            'isAsian' => $this->isAsian,
            'custom_link_class' => $this->customLinkClass,
            'insert_into_content_only' => $this->insertIntoContentOnly,
            'errors' => $this->errors,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->suggestions === []
            && $this->images === []
            && $this->diffs === []
            && $this->brokenLinkFixes === []
            && $this->title === ''
            && $this->metaDescription === ''
            && $this->ogTitle === ''
            && $this->structuredData === ''
            && $this->h1 === '';
    }

    /**
     * @return array<int, mixed>
     */
    private static function arrayOrEmpty(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }
}
