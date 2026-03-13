<?php

declare(strict_types=1);

namespace SEOJuice;

use Generator;
use SEOJuice\Data\PaginatedResult;

final class AutoPaginator
{
    /**
     * Iterate through all pages of a paginated endpoint.
     *
     * Usage:
     *   foreach (AutoPaginator::paginate(fn (int $page, int $pageSize) => $client->pages('example.com')->list(page: $page, pageSize: $pageSize)) as $item) {
     *       echo $item->url;
     *   }
     *
     * @param callable(int, int): PaginatedResult $fetcher A callable that accepts page number and page size, returns a PaginatedResult.
     * @return Generator<int, mixed>
     */
    public static function paginate(callable $fetcher, int $pageSize = 50): Generator
    {
        $page = 1;

        do {
            $result = $fetcher($page, $pageSize);

            foreach ($result->results as $item) {
                yield $item;
            }

            $page++;
        } while ($result->hasNextPage());
    }

    /**
     * Collect all items from all pages into a flat array.
     *
     * @param callable(int, int): PaginatedResult $fetcher
     * @return array<int, mixed>
     */
    public static function all(callable $fetcher, int $pageSize = 50): array
    {
        $items = [];

        foreach (self::paginate($fetcher, $pageSize) as $item) {
            $items[] = $item;
        }

        return $items;
    }
}
