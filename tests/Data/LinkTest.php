<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Data;

use PHPUnit\Framework\TestCase;
use SEOJuice\Data\Link;

final class LinkTest extends TestCase
{
    public function testFromArrayCreatesLinkWithAllProperties(): void
    {
        $data = [
            'page_from' => '/blog/post-1',
            'page_to' => '/contact',
            'keyword' => 'contact us',
            'impressions' => 2500,
            'created_at' => '2025-03-15',
        ];

        $link = Link::fromArray($data);

        $this->assertSame('/blog/post-1', $link->pageFrom);
        $this->assertSame('/contact', $link->pageTo);
        $this->assertSame('contact us', $link->keyword);
        $this->assertSame(2500, $link->impressions);
        $this->assertSame('2025-03-15', $link->createdAt);
    }

    public function testFromArrayHandlesMissingOptionalFields(): void
    {
        $data = [
            'page_from' => '/a',
            'page_to' => '/b',
        ];

        $link = Link::fromArray($data);

        $this->assertSame('/a', $link->pageFrom);
        $this->assertSame('/b', $link->pageTo);
        $this->assertNull($link->keyword);
        $this->assertNull($link->impressions);
        $this->assertNull($link->createdAt);
    }

    public function testFromArrayCastsImpressionsToInt(): void
    {
        $data = [
            'page_from' => '/a',
            'page_to' => '/b',
            'impressions' => '1000',
        ];

        $link = Link::fromArray($data);

        $this->assertSame(1000, $link->impressions);
    }

    public function testFromArrayWithNullImpressions(): void
    {
        $data = [
            'page_from' => '/a',
            'page_to' => '/b',
            'impressions' => null,
        ];

        $link = Link::fromArray($data);

        $this->assertNull($link->impressions);
    }
}
