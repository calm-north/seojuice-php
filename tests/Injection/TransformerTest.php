<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Injection;

use PHPUnit\Framework\TestCase;
use SEOJuice\Injection\Transformer;

final class TransformerTest extends TestCase
{
    public function testEscapeHtmlUsesNumericApostrophe(): void
    {
        $this->assertSame("a&#039;b&lt;c&gt;&amp;&quot;d", Transformer::escapeHtml("a'b<c>&\"d"));
    }

    public function testEscapeHtmlReturnsEmptyStringForEmptyInput(): void
    {
        $this->assertSame('', Transformer::escapeHtml(''));
    }

    public function testNormalizeImageUrlStripsSchemeAndQuery(): void
    {
        $this->assertSame('//x.com/a.png', Transformer::normalizeImageUrl('https://x.com/a.png?w=1'));
        $this->assertSame('//x.com/a.png', Transformer::normalizeImageUrl('http://x.com/a.png'));
    }

    public function testNormalizeImageUrlReturnsEmptyStringForEmptyInput(): void
    {
        $this->assertSame('', Transformer::normalizeImageUrl(''));
    }

    public function testTokenizeSplitsTextAndTags(): void
    {
        $this->assertSame([
            ['type' => 'text', 'value' => 'a'],
            ['type' => 'tag', 'value' => '<b>'],
            ['type' => 'text', 'value' => 'c'],
            ['type' => 'tag', 'value' => '</b>'],
        ], Transformer::tokenizeHtml('a<b>c</b>'));
    }

    public function testTokenizeHandlesLeadingAndTrailingTags(): void
    {
        $this->assertSame([
            ['type' => 'tag', 'value' => '<p>'],
            ['type' => 'text', 'value' => 'x'],
            ['type' => 'tag', 'value' => '</p>'],
        ], Transformer::tokenizeHtml('<p>x</p>'));
    }

    /** @return array{cs: array<int, int>, meta: array<int, string>, img: int, schema: int, h1: int} */
    private function emptyManifest(): array
    {
        return ['cs' => [], 'meta' => [], 'img' => 0, 'schema' => 0, 'h1' => 0];
    }

    public function testReplaceMetaTagsAddsTitleWhenAbsent(): void
    {
        $manifest = $this->emptyManifest();
        $html = Transformer::replaceMetaTags('<html><head></head><body></body></html>', ['title' => 'New Title'], $manifest);

        $this->assertStringContainsString('<title data-seojuice="title">New Title</title>', $html);
        $this->assertSame(['title'], $manifest['meta']);
    }

    public function testReplaceMetaTagsSkipsTitleWhenPresent(): void
    {
        $manifest = $this->emptyManifest();
        $html = Transformer::replaceMetaTags('<html><head><title>Existing</title></head><body></body></html>', ['title' => 'New Title'], $manifest);

        $this->assertStringNotContainsString('New Title', $html);
        $this->assertSame([], $manifest['meta']);
    }

    public function testReplaceMetaTagsAddsDescriptionAndOgTags(): void
    {
        $manifest = $this->emptyManifest();
        $data = [
            'meta_description' => 'Desc',
            'meta_keywords' => 'a,b',
            'og_title' => 'OG T',
            'og_description' => 'OG D',
            'og_url' => 'https://x.com',
            'og_image' => 'https://x.com/i.png',
        ];
        $html = Transformer::replaceMetaTags('<html><head></head><body></body></html>', $data, $manifest);

        $this->assertStringContainsString('<meta name="description" content="Desc" data-seojuice="meta-description">', $html);
        $this->assertStringContainsString('<meta name="keywords" content="a,b" data-seojuice="meta-keywords">', $html);
        $this->assertStringContainsString('<meta property="og:title" content="OG T" data-seojuice="og-title">', $html);
        $this->assertStringContainsString('<meta property="og:description" content="OG D" data-seojuice="og-description">', $html);
        $this->assertStringContainsString('<meta property="og:url" content="https://x.com">', $html);
        $this->assertStringContainsString('<meta property="og:image" content="https://x.com/i.png">', $html);
        $this->assertSame(['meta-description', 'meta-keywords', 'og-title', 'og-description'], $manifest['meta']);
    }

    public function testReplaceMetaTagsDoubleDecodesStructuredData(): void
    {
        $manifest = $this->emptyManifest();
        $raw = json_encode(json_encode(['@type' => 'Article'], JSON_UNESCAPED_SLASHES), JSON_UNESCAPED_SLASHES);
        $html = Transformer::replaceMetaTags('<html><head></head><body></body></html>', ['structured_data' => $raw], $manifest);

        $this->assertStringContainsString('<script type="application/ld+json" data-seojuice="schema">{"@type":"Article"}</script>', $html);
        $this->assertSame(1, $manifest['schema']);
    }

    public function testReplaceMetaTagsSkipsStructuredDataWhenScriptAlreadyPresent(): void
    {
        $manifest = $this->emptyManifest();
        $existing = '<script type="application/ld+json">{}</script>';
        $raw = json_encode(json_encode(['@type' => 'Article']));
        $html = Transformer::replaceMetaTags("<html><head>{$existing}</head><body></body></html>", ['structured_data' => $raw], $manifest);

        $this->assertSame($existing, $this->extractHeadInner($html));
        $this->assertSame(0, $manifest['schema']);
    }

    private function extractHeadInner(string $html): string
    {
        preg_match('/<head>(.*)<\/head>/s', $html, $m);

        return $m[1] ?? '';
    }

    public function testReplaceH1ReplacesInnerTextAndMarksOpenTag(): void
    {
        $manifest = $this->emptyManifest();
        $html = Transformer::replaceH1('<h1>Old</h1>', ['h1' => 'New Heading'], $manifest);

        $this->assertSame('<h1 data-seojuice="h1">New Heading</h1>', $html);
        $this->assertSame(1, $manifest['h1']);
    }

    public function testReplaceH1DoesNothingWhenH1DataAbsent(): void
    {
        $manifest = $this->emptyManifest();
        $html = Transformer::replaceH1('<h1>Old</h1>', ['h1' => ''], $manifest);

        $this->assertSame('<h1>Old</h1>', $html);
        $this->assertSame(0, $manifest['h1']);
    }
}
