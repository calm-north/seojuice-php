<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Data;

use PHPUnit\Framework\TestCase;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\Data\Pagination;

final class PaginatedResultTest extends TestCase
{
    public function testFromArrayCreatesPaginatedResult(): void
    {
        $raw = [
            'pagination' => [
                'page' => 1,
                'page_size' => 10,
                'total_count' => 25,
                'total_pages' => 3,
            ],
            'results' => [
                ['name' => 'Item 1'],
                ['name' => 'Item 2'],
            ],
        ];

        $result = PaginatedResult::fromArray($raw, fn (array $item) => $item['name']);

        $this->assertSame(1, $result->pagination->page);
        $this->assertSame(10, $result->pagination->pageSize);
        $this->assertSame(25, $result->pagination->totalCount);
        $this->assertSame(3, $result->pagination->totalPages);
        $this->assertCount(2, $result->results);
        $this->assertSame('Item 1', $result->results[0]);
        $this->assertSame('Item 2', $result->results[1]);
    }

    public function testFromArrayUsesDataKeyAsFallback(): void
    {
        $raw = [
            'pagination' => [
                'page' => 1,
                'page_size' => 5,
                'total_count' => 1,
                'total_pages' => 1,
            ],
            'data' => [
                ['value' => 'test'],
            ],
        ];

        $result = PaginatedResult::fromArray($raw, fn (array $item) => $item['value']);

        $this->assertCount(1, $result->results);
        $this->assertSame('test', $result->results[0]);
    }

    public function testFromArrayHandlesMissingPaginationKey(): void
    {
        // When 'pagination' key is missing, it uses the raw array itself for pagination
        $raw = [
            'page' => 2,
            'page_size' => 20,
            'total_count' => 100,
            'total_pages' => 5,
            'results' => [
                ['id' => 1],
            ],
        ];

        $result = PaginatedResult::fromArray($raw, fn (array $item) => $item['id']);

        $this->assertSame(2, $result->pagination->page);
        $this->assertSame(20, $result->pagination->pageSize);
        $this->assertSame(100, $result->pagination->totalCount);
    }

    public function testHasNextPageReturnsTrueWhenMorePagesExist(): void
    {
        $pagination = new Pagination(page: 1, pageSize: 10, totalCount: 25, totalPages: 3);
        $result = new PaginatedResult($pagination, []);

        $this->assertTrue($result->hasNextPage());
    }

    public function testHasNextPageReturnsFalseOnLastPage(): void
    {
        $pagination = new Pagination(page: 3, pageSize: 10, totalCount: 25, totalPages: 3);
        $result = new PaginatedResult($pagination, []);

        $this->assertFalse($result->hasNextPage());
    }

    public function testHasPreviousPageReturnsTrueAfterFirstPage(): void
    {
        $pagination = new Pagination(page: 2, pageSize: 10, totalCount: 25, totalPages: 3);
        $result = new PaginatedResult($pagination, []);

        $this->assertTrue($result->hasPreviousPage());
    }

    public function testHasPreviousPageReturnsFalseOnFirstPage(): void
    {
        $pagination = new Pagination(page: 1, pageSize: 10, totalCount: 25, totalPages: 3);
        $result = new PaginatedResult($pagination, []);

        $this->assertFalse($result->hasPreviousPage());
    }

    public function testFromArrayWithEmptyResults(): void
    {
        $raw = [
            'pagination' => [
                'page' => 1,
                'page_size' => 10,
                'total_count' => 0,
                'total_pages' => 0,
            ],
            'results' => [],
        ];

        $result = PaginatedResult::fromArray($raw, fn (array $item) => $item);

        $this->assertCount(0, $result->results);
        $this->assertSame(0, $result->pagination->totalCount);
        $this->assertFalse($result->hasNextPage());
        $this->assertFalse($result->hasPreviousPage());
    }
}
