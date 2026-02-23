<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Data;

use PHPUnit\Framework\TestCase;
use SEOJuice\Data\Link;
use SEOJuice\Data\Page;

final class PageTest extends TestCase
{
    public function testFromArrayCreatesPageWithAllProperties(): void
    {
        $data = [
            'id' => 42,
            'url' => 'https://example.com/about',
            'title' => 'About Us',
            'page_type' => 'article',
            'seo_score' => 88.5,
            'accessibility_score' => 75.0,
            'onpage_score' => 90.0,
            'conversion_score' => 65.0,
            'meta_description' => 'Learn about our company',
            'language_code' => 'en',
            'og_title' => 'About - Example',
            'og_description' => 'OG description here',
            'og_image' => 'https://example.com/og.jpg',
            'readability' => ['score' => 70, 'grade' => 'B'],
            'structured_data' => [['@type' => 'Article']],
            'links' => [
                [
                    'page_from' => '/about',
                    'page_to' => '/contact',
                    'keyword' => 'contact us',
                    'impressions' => 1500,
                    'created_at' => '2025-01-01',
                ],
            ],
            'created_at' => '2025-01-15',
            'last_processed_at' => '2025-06-01',
        ];

        $page = Page::fromArray($data);

        $this->assertSame(42, $page->id);
        $this->assertSame('https://example.com/about', $page->url);
        $this->assertSame('About Us', $page->title);
        $this->assertSame('article', $page->pageType);
        $this->assertSame(88.5, $page->seoScore);
        $this->assertSame(75.0, $page->accessibilityScore);
        $this->assertSame(90.0, $page->onpageScore);
        $this->assertSame(65.0, $page->conversionScore);
        $this->assertSame('Learn about our company', $page->metaDescription);
        $this->assertSame('en', $page->languageCode);
        $this->assertSame('About - Example', $page->ogTitle);
        $this->assertSame('OG description here', $page->ogDescription);
        $this->assertSame('https://example.com/og.jpg', $page->ogImage);
        $this->assertSame(['score' => 70, 'grade' => 'B'], $page->readability);
        $this->assertSame([['@type' => 'Article']], $page->structuredData);
        $this->assertCount(1, $page->links);
        $this->assertInstanceOf(Link::class, $page->links[0]);
        $this->assertSame('/about', $page->links[0]->pageFrom);
        $this->assertSame('/contact', $page->links[0]->pageTo);
        $this->assertSame('2025-01-15', $page->createdAt);
        $this->assertSame('2025-06-01', $page->lastProcessedAt);
    }

    public function testFromArrayHandlesMissingOptionalFields(): void
    {
        $data = [
            'id' => 1,
            'url' => '/home',
        ];

        $page = Page::fromArray($data);

        $this->assertSame(1, $page->id);
        $this->assertSame('/home', $page->url);
        $this->assertNull($page->title);
        $this->assertNull($page->pageType);
        $this->assertNull($page->seoScore);
        $this->assertNull($page->accessibilityScore);
        $this->assertNull($page->onpageScore);
        $this->assertNull($page->conversionScore);
        $this->assertNull($page->metaDescription);
        $this->assertNull($page->languageCode);
        $this->assertNull($page->ogTitle);
        $this->assertNull($page->ogDescription);
        $this->assertNull($page->ogImage);
        $this->assertSame([], $page->readability);
        $this->assertSame([], $page->structuredData);
        $this->assertSame([], $page->links);
        $this->assertNull($page->createdAt);
        $this->assertNull($page->lastProcessedAt);
    }

    public function testFromArrayCastsIdToInt(): void
    {
        $data = [
            'id' => '99',
            'url' => '/test',
        ];

        $page = Page::fromArray($data);

        $this->assertSame(99, $page->id);
    }

    public function testFromArrayCastsScoresToFloat(): void
    {
        $data = [
            'id' => 1,
            'url' => '/test',
            'seo_score' => '85',
            'accessibility_score' => '70',
            'onpage_score' => '90',
            'conversion_score' => '60',
        ];

        $page = Page::fromArray($data);

        $this->assertSame(85.0, $page->seoScore);
        $this->assertSame(70.0, $page->accessibilityScore);
        $this->assertSame(90.0, $page->onpageScore);
        $this->assertSame(60.0, $page->conversionScore);
    }

    public function testFromArrayWithMultipleLinks(): void
    {
        $data = [
            'id' => 1,
            'url' => '/test',
            'links' => [
                ['page_from' => '/a', 'page_to' => '/b', 'keyword' => 'k1', 'impressions' => 100, 'created_at' => null],
                ['page_from' => '/c', 'page_to' => '/d', 'keyword' => null, 'impressions' => null, 'created_at' => null],
            ],
        ];

        $page = Page::fromArray($data);

        $this->assertCount(2, $page->links);
        $this->assertInstanceOf(Link::class, $page->links[0]);
        $this->assertInstanceOf(Link::class, $page->links[1]);
        $this->assertSame('/a', $page->links[0]->pageFrom);
        $this->assertSame('/c', $page->links[1]->pageFrom);
    }

    public function testFromArrayWithNullScores(): void
    {
        $data = [
            'id' => 1,
            'url' => '/test',
            'seo_score' => null,
            'accessibility_score' => null,
        ];

        $page = Page::fromArray($data);

        $this->assertNull($page->seoScore);
        $this->assertNull($page->accessibilityScore);
    }
}
