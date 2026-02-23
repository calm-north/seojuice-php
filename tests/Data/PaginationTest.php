<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Data;

use PHPUnit\Framework\TestCase;
use SEOJuice\Data\Pagination;

final class PaginationTest extends TestCase
{
    public function testFromArrayCreatesPaginationWithAllProperties(): void
    {
        $data = [
            'page' => 3,
            'page_size' => 25,
            'total_count' => 100,
            'total_pages' => 4,
        ];

        $pagination = Pagination::fromArray($data);

        $this->assertSame(3, $pagination->page);
        $this->assertSame(25, $pagination->pageSize);
        $this->assertSame(100, $pagination->totalCount);
        $this->assertSame(4, $pagination->totalPages);
    }

    public function testFromArrayDefaultsToSensibleValues(): void
    {
        $pagination = Pagination::fromArray([]);

        $this->assertSame(1, $pagination->page);
        $this->assertSame(10, $pagination->pageSize);
        $this->assertSame(0, $pagination->totalCount);
        $this->assertSame(0, $pagination->totalPages);
    }

    public function testFromArrayCastsValuesToInt(): void
    {
        $data = [
            'page' => '2',
            'page_size' => '15',
            'total_count' => '50',
            'total_pages' => '4',
        ];

        $pagination = Pagination::fromArray($data);

        $this->assertSame(2, $pagination->page);
        $this->assertSame(15, $pagination->pageSize);
        $this->assertSame(50, $pagination->totalCount);
        $this->assertSame(4, $pagination->totalPages);
    }

    public function testFromArrayWithPartialData(): void
    {
        $data = [
            'page' => 5,
            'total_count' => 200,
        ];

        $pagination = Pagination::fromArray($data);

        $this->assertSame(5, $pagination->page);
        $this->assertSame(10, $pagination->pageSize); // default
        $this->assertSame(200, $pagination->totalCount);
        $this->assertSame(0, $pagination->totalPages); // default
    }
}
