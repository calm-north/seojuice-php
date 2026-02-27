<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class SimilarPagesResult
{
    /** @var array{url: string, title: string} */
    public readonly array $source;
    /** @var list<SimilarPage> */
    public readonly array $similarPages;

    /**
     * @param array{url: string, title: string} $source
     * @param list<SimilarPage> $similarPages
     */
    public function __construct(
        array $source,
        array $similarPages,
    ) {
        $this->source = $source;
        $this->similarPages = $similarPages;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $pages = array_map(
            fn (array $item) => SimilarPage::fromArray($item),
            $data['similar_pages'] ?? [],
        );

        return new self(
            source: $data['source'] ?? ['url' => '', 'title' => ''],
            similarPages: $pages,
        );
    }
}
