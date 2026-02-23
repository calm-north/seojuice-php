<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class GbpLocation
{
    public readonly int $id;
    public readonly string $locationId;
    public readonly string $name;
    public readonly ?string $address;
    public readonly ?string $phone;
    public readonly ?float $averageRating;
    public readonly int $totalReviews;
    public readonly ?string $lastFetchedAt;

    public function __construct(
        int $id,
        string $locationId,
        string $name,
        ?string $address,
        ?string $phone,
        ?float $averageRating,
        int $totalReviews,
        ?string $lastFetchedAt,
    ) {
        $this->id = $id;
        $this->locationId = $locationId;
        $this->name = $name;
        $this->address = $address;
        $this->phone = $phone;
        $this->averageRating = $averageRating;
        $this->totalReviews = $totalReviews;
        $this->lastFetchedAt = $lastFetchedAt;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            locationId: (string) $data['location_id'],
            name: (string) $data['name'],
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            averageRating: isset($data['average_rating']) ? (float) $data['average_rating'] : null,
            totalReviews: (int) ($data['total_reviews'] ?? 0),
            lastFetchedAt: $data['last_fetched_at'] ?? null,
        );
    }
}
