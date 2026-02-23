<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\GbpLocation;
use SEOJuice\Data\GbpReview;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\HttpClient;

final class GbpResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    /**
     * @return array<int, GbpLocation>
     */
    public function locations(): array
    {
        $data = $this->http->get("websites/{$this->domain}/gbp/locations/");

        $locations = $data['results'] ?? $data;

        return array_map(
            fn (array $item) => GbpLocation::fromArray($item),
            $locations,
        );
    }

    public function reviews(
        ?int $rating = null,
        ?string $sentiment = null,
        ?bool $needsAttention = null,
        ?int $locationId = null,
        int $page = 1,
        int $pageSize = 10,
    ): PaginatedResult {
        $data = $this->http->get("websites/{$this->domain}/gbp/reviews/", [
            'rating' => $rating,
            'sentiment' => $sentiment,
            'needs_attention' => $needsAttention !== null ? ($needsAttention ? 'true' : 'false') : null,
            'location_id' => $locationId,
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => GbpReview::fromArray($item));
    }

    /**
     * @return array<string, mixed>
     */
    public function replyToReview(int $reviewId, string $replyText): array
    {
        return $this->http->post("websites/{$this->domain}/gbp/reviews/{$reviewId}/reply/", [
            'reply_text' => $replyText,
        ]);
    }
}
