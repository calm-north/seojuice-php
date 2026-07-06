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

    public function testReplaceImagesFillsEmptyAltByNormalizedUrl(): void
    {
        $manifest = $this->emptyManifest();
        $data = ['images' => [['url' => 'https://x.com/a.png', 'alt_text' => 'A lovely photo']]];
        $html = Transformer::replaceImages('<img src="//x.com/a.png" alt="">', $data, $manifest);

        $this->assertSame('<img data-seojuice="alt" src="//x.com/a.png" alt="A lovely photo">', $html);
        $this->assertSame(1, $manifest['img']);
    }

    public function testReplaceImagesKeepsGoodExistingAlt(): void
    {
        $manifest = $this->emptyManifest();
        $data = ['images' => [['url' => 'https://x.com/a.png', 'alt_text' => 'Replacement text']]];
        $html = Transformer::replaceImages('<img src="//x.com/a.png" alt="A perfectly fine description">', $data, $manifest);

        $this->assertSame('<img src="//x.com/a.png" alt="A perfectly fine description">', $html);
        $this->assertSame(0, $manifest['img']);
    }

    public function testReplaceImagesReplacesShortAlt(): void
    {
        $manifest = $this->emptyManifest();
        $data = ['images' => [['url' => 'https://x.com/a.png', 'alt_text' => 'Good alt text']]];
        $html = Transformer::replaceImages('<img src="//x.com/a.png" alt="hi">', $data, $manifest);

        $this->assertSame('<img data-seojuice="alt" src="//x.com/a.png" alt="Good alt text">', $html);
        $this->assertSame(1, $manifest['img']);
    }

    public function testReplaceImagesAddsAltWhenAttributeMissing(): void
    {
        $manifest = $this->emptyManifest();
        $data = ['images' => [['url' => 'https://x.com/a.png', 'alt_text' => 'Good alt text']]];
        $html = Transformer::replaceImages('<img src="//x.com/a.png">', $data, $manifest);

        $this->assertSame('<img alt="Good alt text" data-seojuice="alt" src="//x.com/a.png">', $html);
        $this->assertSame(1, $manifest['img']);
    }

    public function testReplaceImagesReturnsUnchangedWhenNoImagesData(): void
    {
        $manifest = $this->emptyManifest();
        $html = Transformer::replaceImages('<img src="//x.com/a.png">', [], $manifest);

        $this->assertSame('<img src="//x.com/a.png">', $html);
        $this->assertSame(0, $manifest['img']);
    }

    public function testInjectInternalLinksFirstOccurrenceOnlyWithCsMarker(): void
    {
        $manifest = $this->emptyManifest();
        $data = [
            'suggestions' => [['keyword' => 'widget', 'url' => '/widgets', 'id' => 42]],
            'custom_link_class' => '',
        ];
        $out = Transformer::injectInternalLinks('<p>Buy a widget. Another widget is here.</p>', $data, $manifest);

        $this->assertSame(
            '<p>Buy a <a href="/widgets" data-seojuice-cs="42">widget</a>. Another widget is here.</p>',
            $out,
        );
        $this->assertSame([42], $manifest['cs']);
    }

    public function testInjectInternalLinksNeverInsideAnchorOrHeading(): void
    {
        $manifest = $this->emptyManifest();
        $data = [
            'suggestions' => [['keyword' => 'widget', 'url' => '/widgets', 'id' => 1]],
            'custom_link_class' => '',
        ];
        $out = Transformer::injectInternalLinks(
            '<h1>widget</h1><a href="/x">widget</a><p>widget</p>',
            $data,
            $manifest,
        );

        $this->assertSame(
            '<h1>widget</h1><a href="/x">widget</a><p><a href="/widgets" data-seojuice-cs="1">widget</a></p>',
            $out,
        );
    }

    public function testInjectInternalLinksAppliesCustomLinkClass(): void
    {
        $manifest = $this->emptyManifest();
        $data = [
            'suggestions' => [['keyword' => 'widget', 'url' => '/widgets', 'id' => 1]],
            'custom_link_class' => 'my-cls',
        ];
        $out = Transformer::injectInternalLinks('<p>widget</p>', $data, $manifest);

        $this->assertSame('<p><a href="/widgets" class="seojuice-link my-cls" data-seojuice-cs="1">widget</a></p>', $out);
    }

    public function testLinksChineseKeywordBetweenCjkChars(): void
    {
        $manifest = $this->emptyManifest();
        $data = ['suggestions' => [['keyword' => '投资基金', 'url' => '/funds', 'id' => 501]], 'isAsian' => true, 'custom_link_class' => ''];
        $out = Transformer::injectInternalLinks('<p>我想了解投资基金的收益。</p>', $data, $manifest);

        $this->assertSame('<p>我想了解<a href="/funds" data-seojuice-cs="501">投资基金</a>的收益。</p>', $out);
    }

    public function testLinksJapaneseKeyword(): void
    {
        $manifest = $this->emptyManifest();
        $data = ['suggestions' => [['keyword' => '投資信託', 'url' => '/toushin', 'id' => 777]], 'isAsian' => true, 'custom_link_class' => ''];
        $out = Transformer::injectInternalLinks('<p>私は投資信託を学ぶ。</p>', $data, $manifest);

        $this->assertStringContainsString('<a href="/toushin" data-seojuice-cs="777">投資信託</a>', $out);
    }
}
