<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class SimilarPage
{
    public readonly string $url;
    public readonly string $title;
    public readonly float $similarity;
    public readonly ?string $cluster;

    public function __construct(
        string $url,
        string $title,
        float $similarity,
        ?string $cluster,
    ) {
        $this->url = $url;
        $this->title = $title;
        $this->similarity = $similarity;
        $this->cluster = $cluster;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            url: (string) $data['url'],
            title: (string) ($data['title'] ?? ''),
            similarity: (float) ($data['similarity'] ?? 0),
            cluster: $data['cluster'] ?? null,
        );
    }
}
