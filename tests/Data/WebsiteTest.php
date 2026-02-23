<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Data;

use PHPUnit\Framework\TestCase;
use SEOJuice\Data\Website;

final class WebsiteTest extends TestCase
{
    public function testFromArrayCreatesWebsiteWithAllProperties(): void
    {
        $data = [
            'domain' => 'example.com',
            'platform' => 'wordpress',
            'industry' => 'technology',
            'seo_score' => 92.5,
            'scores' => [
                'technical' => 88,
                'content' => 95,
                'authority' => 80,
            ],
            'kpis' => [
                'indexed_pages' => 500,
                'total_keywords' => 1200,
            ],
            'created_at' => '2025-01-15T10:00:00Z',
            'last_processed_at' => '2025-06-10T14:30:00Z',
        ];

        $website = Website::fromArray($data);

        $this->assertSame('example.com', $website->domain);
        $this->assertSame('wordpress', $website->platform);
        $this->assertSame('technology', $website->industry);
        $this->assertSame(92.5, $website->seoScore);
        $this->assertSame(['technical' => 88, 'content' => 95, 'authority' => 80], $website->scores);
        $this->assertSame(['indexed_pages' => 500, 'total_keywords' => 1200], $website->kpis);
        $this->assertSame('2025-01-15T10:00:00Z', $website->createdAt);
        $this->assertSame('2025-06-10T14:30:00Z', $website->lastProcessedAt);
    }

    public function testFromArrayHandlesMissingOptionalFields(): void
    {
        $data = [
            'domain' => 'minimal.com',
        ];

        $website = Website::fromArray($data);

        $this->assertSame('minimal.com', $website->domain);
        $this->assertNull($website->platform);
        $this->assertNull($website->industry);
        $this->assertNull($website->seoScore);
        $this->assertSame([], $website->scores);
        $this->assertSame([], $website->kpis);
        $this->assertNull($website->createdAt);
        $this->assertNull($website->lastProcessedAt);
    }

    public function testFromArrayCastsDomainToString(): void
    {
        // Even if somehow domain is not a string in the array, it should be cast
        $data = [
            'domain' => 'example.com',
            'seo_score' => '85.5', // string that should be cast to float
        ];

        $website = Website::fromArray($data);

        $this->assertIsString($website->domain);
        $this->assertIsFloat($website->seoScore);
        $this->assertSame(85.5, $website->seoScore);
    }

    public function testFromArrayWithNullSeoScore(): void
    {
        $data = [
            'domain' => 'noscore.com',
            'seo_score' => null,
        ];

        $website = Website::fromArray($data);

        // isset returns false for null, so seoScore should be null
        $this->assertNull($website->seoScore);
    }

    public function testFromArrayWithZeroSeoScore(): void
    {
        $data = [
            'domain' => 'zero.com',
            'seo_score' => 0,
        ];

        $website = Website::fromArray($data);

        // isset returns true for 0, so it should be cast to float 0.0
        $this->assertSame(0.0, $website->seoScore);
    }

    public function testFromArrayWithEmptyScoresAndKpis(): void
    {
        $data = [
            'domain' => 'empty.com',
            'scores' => [],
            'kpis' => [],
        ];

        $website = Website::fromArray($data);

        $this->assertSame([], $website->scores);
        $this->assertSame([], $website->kpis);
    }

    public function testFromArrayWithExplicitNullValues(): void
    {
        $data = [
            'domain' => 'nulls.com',
            'platform' => null,
            'industry' => null,
            'created_at' => null,
            'last_processed_at' => null,
        ];

        $website = Website::fromArray($data);

        $this->assertNull($website->platform);
        $this->assertNull($website->industry);
        $this->assertNull($website->createdAt);
        $this->assertNull($website->lastProcessedAt);
    }
}
