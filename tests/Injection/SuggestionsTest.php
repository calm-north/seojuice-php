<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Injection;

use PHPUnit\Framework\TestCase;
use SEOJuice\Injection\Suggestions;

final class SuggestionsTest extends TestCase
{
    public function testFromArrayCreatesInstanceFromFullApiResponse(): void
    {
        $data = [
            'links' => [
                ['href' => '/about', 'anchor' => 'About us'],
            ],
            'images' => [
                ['src' => '/img/logo.png', 'alt' => 'Logo'],
            ],
            'meta_tags' => [
                'title' => 'My Page Title',
                'description' => 'My description',
            ],
            'structured_data' => [
                ['@type' => 'Organization', 'name' => 'Test'],
            ],
            'accessibility_fixes' => [
                ['type' => 'aria-label', 'selector' => '#nav'],
            ],
            'og_tags' => [
                'title' => 'OG Title',
                'description' => 'OG Description',
            ],
        ];

        $suggestions = Suggestions::fromArray($data);

        $this->assertCount(1, $suggestions->links);
        $this->assertSame('/about', $suggestions->links[0]['href']);

        $this->assertCount(1, $suggestions->images);
        $this->assertSame('/img/logo.png', $suggestions->images[0]['src']);

        $this->assertSame('My Page Title', $suggestions->metaTags['title']);
        $this->assertSame('My description', $suggestions->metaTags['description']);

        $this->assertCount(1, $suggestions->structuredData);
        $this->assertSame('Organization', $suggestions->structuredData[0]['@type']);

        $this->assertCount(1, $suggestions->accessibilityFixes);
        $this->assertSame('aria-label', $suggestions->accessibilityFixes[0]['type']);

        $this->assertSame('OG Title', $suggestions->ogTags['title']);
        $this->assertSame('OG Description', $suggestions->ogTags['description']);
    }

    public function testFromArrayHandlesMissingKeysWithDefaults(): void
    {
        $suggestions = Suggestions::fromArray([]);

        $this->assertSame([], $suggestions->links);
        $this->assertSame([], $suggestions->images);
        $this->assertSame([], $suggestions->metaTags);
        $this->assertSame([], $suggestions->structuredData);
        $this->assertSame([], $suggestions->accessibilityFixes);
        $this->assertSame([], $suggestions->ogTags);
    }

    public function testFromArrayHandlesPartialData(): void
    {
        $data = [
            'meta_tags' => ['title' => 'Only Title'],
            'og_tags' => ['title' => 'OG Only'],
        ];

        $suggestions = Suggestions::fromArray($data);

        $this->assertSame([], $suggestions->links);
        $this->assertSame([], $suggestions->images);
        $this->assertSame(['title' => 'Only Title'], $suggestions->metaTags);
        $this->assertSame([], $suggestions->structuredData);
        $this->assertSame([], $suggestions->accessibilityFixes);
        $this->assertSame(['title' => 'OG Only'], $suggestions->ogTags);
    }

    public function testIsEmptyReturnsTrueWhenAllArraysAreEmpty(): void
    {
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $this->assertTrue($suggestions->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenLinksHasData(): void
    {
        $suggestions = new Suggestions(
            links: [['href' => '/about']],
            images: [],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $this->assertFalse($suggestions->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenImagesHasData(): void
    {
        $suggestions = new Suggestions(
            links: [],
            images: [['src' => '/img.png', 'alt' => 'test']],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $this->assertFalse($suggestions->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenMetaTagsHasData(): void
    {
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: ['title' => 'Test'],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $this->assertFalse($suggestions->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenStructuredDataHasData(): void
    {
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: [],
            structuredData: [['@type' => 'WebSite']],
            accessibilityFixes: [],
            ogTags: [],
        );

        $this->assertFalse($suggestions->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenAccessibilityFixesHasData(): void
    {
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [['type' => 'aria-label']],
            ogTags: [],
        );

        $this->assertFalse($suggestions->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenOgTagsHasData(): void
    {
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: ['title' => 'OG Title'],
        );

        $this->assertFalse($suggestions->isEmpty());
    }

    public function testPropertiesAreReadonly(): void
    {
        $suggestions = Suggestions::fromArray([
            'links' => [['href' => '/test']],
            'meta_tags' => ['title' => 'Test'],
        ]);

        // Verify properties are accessible
        $this->assertCount(1, $suggestions->links);
        $this->assertSame('Test', $suggestions->metaTags['title']);
    }
}
