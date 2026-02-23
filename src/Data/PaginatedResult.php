<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class PaginatedResult
{
    public readonly Pagination $pagination;

    /** @var array<int, mixed> */
    public readonly array $results;

    /**
     * @param array<int, mixed> $results
     */
    public function __construct(Pagination $pagination, array $results)
    {
        $this->pagination = $pagination;
        $this->results = $results;
    }

    public function hasNextPage(): bool
    {
        return $this->pagination->page < $this->pagination->totalPages;
    }

    public function hasPreviousPage(): bool
    {
        return $this->pagination->page > 1;
    }

    /**
     * @param array<string, mixed> $raw
     * @param callable(array<string, mixed>): mixed $hydrator
     */
    public static function fromArray(array $raw, callable $hydrator): self
    {
        $pagination = Pagination::fromArray($raw['pagination'] ?? $raw);

        $items = $raw['results'] ?? $raw['data'] ?? [];
        $results = array_map($hydrator, $items);

        return new self($pagination, $results);
    }
}
