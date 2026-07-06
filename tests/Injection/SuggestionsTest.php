<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Injection;

use PHPUnit\Framework\TestCase;
use SEOJuice\Injection\Suggestions;

final class SuggestionsTest extends TestCase
{
    public function testFromArrayAcceptsStringStructuredDataWithoutCrash(): void
    {
        $s = Suggestions::fromArray(['structured_data' => '"{\"@type\":\"Article\"}"', 'suggestions' => []]);

        $this->assertIsString($s->toArray()['structured_data']);
    }

    public function testFromArrayMapsRealKeys(): void
    {
        $s = Suggestions::fromArray(['suggestions' => [['keyword' => 'a', 'url' => '/a', 'id' => 1]], 'title' => 'T']);

        $this->assertFalse($s->isEmpty());
        $this->assertSame('T', $s->toArray()['title']);
    }

    public function testFromArrayCreatesInstanceFromFullApiResponse(): void
    {
        $data = [
            'suggestions' => [['keyword' => 'about', 'url' => '/about', 'id' => 1]],
            'images' => [['url' => '/img/logo.png', 'alt_text' => 'Logo']],
            'diffs' => [['id' => 9, 'original_text' => 'a', 'replacement_html' => 'b']],
            'broken_link_fixes' => [['broken_url' => '/dead', 'new_url' => '/live', 'tag' => 'a', 'attr' => 'href']],
            'title' => 'My Page Title',
            'meta_description' => 'My description',
            'meta_keywords' => 'kw1,kw2',
            'og_title' => 'OG Title',
            'og_description' => 'OG Description',
            'og_image' => '/og.png',
            'structured_data' => '"{\"@type\":\"Organization\"}"',
            'h1' => 'Heading',
            'isAsian' => true,
            'custom_link_class' => 'my-link',
            'insert_into_content_only' => true,
            'errors' => [],
        ];

        $suggestions = Suggestions::fromArray($data);

        $this->assertCount(1, $suggestions->suggestions);
        $this->assertSame('/about', $suggestions->suggestions[0]['url']);
        $this->assertCount(1, $suggestions->images);
        $this->assertSame('/img/logo.png', $suggestions->images[0]['url']);
        $this->assertCount(1, $suggestions->diffs);
        $this->assertCount(1, $suggestions->brokenLinkFixes);
        $this->assertSame('My Page Title', $suggestions->title);
        $this->assertSame('My description', $suggestions->metaDescription);
        $this->assertSame('OG Title', $suggestions->ogTitle);
        $this->assertSame('Heading', $suggestions->h1);
        $this->assertTrue($suggestions->isAsian);
        $this->assertSame('my-link', $suggestions->customLinkClass);
        $this->assertTrue($suggestions->insertIntoContentOnly);
    }

    public function testFromArrayHandlesMissingKeysWithDefaults(): void
    {
        $suggestions = Suggestions::fromArray([]);

        $this->assertSame([], $suggestions->suggestions);
        $this->assertSame([], $suggestions->images);
        $this->assertSame([], $suggestions->diffs);
        $this->assertSame([], $suggestions->brokenLinkFixes);
        $this->assertSame('', $suggestions->title);
        $this->assertSame('', $suggestions->structuredData);
        $this->assertFalse($suggestions->isAsian);
        $this->assertFalse($suggestions->insertIntoContentOnly);
        $this->assertTrue($suggestions->isEmpty());
    }

    public function testFromArrayCoercesNonArraySuggestionsToEmpty(): void
    {
        $suggestions = Suggestions::fromArray(['suggestions' => 'not-an-array']);

        $this->assertSame([], $suggestions->suggestions);
    }

    public function testFromArrayHandlesPartialData(): void
    {
        $data = [
            'title' => 'Only Title',
            'og_title' => 'OG Only',
        ];

        $suggestions = Suggestions::fromArray($data);

        $this->assertSame([], $suggestions->suggestions);
        $this->assertSame([], $suggestions->images);
        $this->assertSame('Only Title', $suggestions->title);
        $this->assertSame('OG Only', $suggestions->ogTitle);
    }

    public function testIsEmptyReturnsTrueWhenNothingActionable(): void
    {
        $suggestions = Suggestions::fromArray([]);

        $this->assertTrue($suggestions->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenSuggestionsHasData(): void
    {
        $suggestions = Suggestions::fromArray(['suggestions' => [['keyword' => 'a', 'url' => '/a']]]);

        $this->assertFalse($suggestions->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenImagesHasData(): void
    {
        $suggestions = Suggestions::fromArray(['images' => [['url' => '/img.png', 'alt_text' => 'test']]]);

        $this->assertFalse($suggestions->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenDiffsHasData(): void
    {
        $suggestions = Suggestions::fromArray(['diffs' => [['original_text' => 'a', 'replacement_html' => 'b']]]);

        $this->assertFalse($suggestions->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenTitleHasData(): void
    {
        $suggestions = Suggestions::fromArray(['title' => 'Test']);

        $this->assertFalse($suggestions->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenStructuredDataHasData(): void
    {
        $suggestions = Suggestions::fromArray(['structured_data' => '"{\"@type\":\"WebSite\"}"']);

        $this->assertFalse($suggestions->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenOgTitleHasData(): void
    {
        $suggestions = Suggestions::fromArray(['og_title' => 'OG Title']);

        $this->assertFalse($suggestions->isEmpty());
    }

    public function testToArrayRoundTripsRealKeys(): void
    {
        $data = [
            'suggestions' => [['keyword' => 'a', 'url' => '/a', 'id' => 1]],
            'title' => 'T',
            'isAsian' => true,
        ];

        $roundTripped = Suggestions::fromArray($data)->toArray();

        $this->assertSame($data['suggestions'], $roundTripped['suggestions']);
        $this->assertSame('T', $roundTripped['title']);
        $this->assertTrue($roundTripped['isAsian']);
    }

    public function testPropertiesAreReadonly(): void
    {
        $suggestions = Suggestions::fromArray([
            'suggestions' => [['keyword' => 'a', 'url' => '/a']],
            'title' => 'Test',
        ]);

        $this->assertCount(1, $suggestions->suggestions);
        $this->assertSame('Test', $suggestions->title);
    }
}
