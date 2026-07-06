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

    public function testReplaceMetaTagsInjectsDollarAndBackslashTextVerbatim(): void
    {
        // htmlspecialchars does not neutralize `$` or `\`; if the replacement were
        // interpolated, `$5`/`$1` (nonexistent groups) would be dropped and
        // `\0`/`$0` would splice in the whole match (</head>). Assert verbatim.
        $manifest = $this->emptyManifest();
        $data = [
            'title' => 'Save $5 now $1 deal',
            'meta_description' => 'Deal \0 and \1 backref $0 test',
        ];
        $html = Transformer::replaceMetaTags('<html><head></head><body></body></html>', $data, $manifest);

        $this->assertStringContainsString('<title data-seojuice="title">Save $5 now $1 deal</title>', $html);
        $this->assertStringContainsString('content="Deal \0 and \1 backref $0 test" data-seojuice="meta-description"', $html);
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

    public function testReplaceImagesInjectsDollarBackslashAltVerbatimWhenReplacingShortAlt(): void
    {
        $manifest = $this->emptyManifest();
        $data = ['images' => [['url' => 'https://x.com/a.png', 'alt_text' => 'Cost $5 or $1 \0 \1']]];
        $html = Transformer::replaceImages('<img src="//x.com/a.png" alt="hi">', $data, $manifest);

        $this->assertStringContainsString('alt="Cost $5 or $1 \0 \1"', $html);
        $this->assertSame(1, $manifest['img']);
    }

    public function testReplaceImagesInjectsDollarBackslashAltVerbatimWhenAltMissing(): void
    {
        $manifest = $this->emptyManifest();
        $data = ['images' => [['url' => 'https://x.com/a.png', 'alt_text' => 'Price $9 back\0slash']]];
        $html = Transformer::replaceImages('<img src="//x.com/a.png">', $data, $manifest);

        $this->assertStringContainsString('alt="Price $9 back\0slash"', $html);
        $this->assertSame(1, $manifest['img']);
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

    public function testApplyContentDiffsAppliesUniqueDiffWithMarker(): void
    {
        $manifest = $this->emptyManifest();
        $diffs = [['id' => 9, 'original_text' => '<p>old copy</p>', 'replacement_html' => '<p>new copy</p>']];
        $html = Transformer::applyContentDiffs('<div><p>old copy</p></div>', $diffs, $manifest);

        $this->assertSame('<div><p data-seojuice-cs="9">new copy</p></div>', $html);
        $this->assertSame([9], $manifest['cs']);
    }

    public function testApplyContentDiffsSkipsAmbiguousMatch(): void
    {
        $manifest = $this->emptyManifest();
        $diffs = [['id' => 1, 'original_text' => 'dup', 'replacement_html' => 'X']];
        $html = Transformer::applyContentDiffs('<p>dup dup</p>', $diffs, $manifest);

        $this->assertSame('<p>dup dup</p>', $html);
        $this->assertSame([], $manifest['cs']);
    }

    public function testApplyContentDiffsSkipsWhenOriginalTextDrifted(): void
    {
        $manifest = $this->emptyManifest();
        $diffs = [['id' => 1, 'original_text' => 'no longer here', 'replacement_html' => 'X']];
        $html = Transformer::applyContentDiffs('<p>completely different content</p>', $diffs, $manifest);

        $this->assertSame('<p>completely different content</p>', $html);
        $this->assertSame([], $manifest['cs']);
    }

    public function testApplyContentDiffsIsIdempotentWhenAlreadyApplied(): void
    {
        $manifest = $this->emptyManifest();
        $diffs = [['id' => 1, 'original_text' => '<p>old</p>', 'replacement_html' => '<p>new</p>']];
        $html = Transformer::applyContentDiffs('<div><p>new</p></div>', $diffs, $manifest);

        $this->assertSame('<div><p>new</p></div>', $html);
        $this->assertSame([], $manifest['cs']);
    }

    public function testApplyBrokenLinkFixesReplacesViaEdgeNewUrl(): void
    {
        $fixes = [['tag' => 'a', 'attr' => 'href', 'broken_url' => '/dead', 'new_url' => '/live', 'action' => 'replace']];
        $html = Transformer::applyBrokenLinkFixes('<a href="/dead">link</a>', $fixes);

        $this->assertSame('<a href="/live">link</a>', $html);
    }

    public function testApplyBrokenLinkFixesReplacesViaLegacyReplacementUrlWhenNewUrlEmpty(): void
    {
        $fixes = [[
            'tag' => 'a', 'attr' => 'href', 'broken_url' => '/dead', 'new_url' => '',
            'replacement_url' => '/live-legacy', 'action' => 'replace',
        ]];
        $html = Transformer::applyBrokenLinkFixes('<a href="/dead">link</a>', $fixes);

        $this->assertSame('<a href="/live-legacy">link</a>', $html);
    }

    public function testApplyBrokenLinkFixesUnlinkRemovesWholeAnchor(): void
    {
        $fixes = [['tag' => 'a', 'attr' => 'href', 'broken_url' => '/dead', 'action' => 'unlink']];
        $html = Transformer::applyBrokenLinkFixes('<p>See <a href="/dead">this</a> now.</p>', $fixes);

        $this->assertSame('<p>See  now.</p>', $html);
    }

    public function testApplyBrokenLinkFixesLeavesDataHrefUntouched(): void
    {
        $fixes = [['tag' => 'a', 'attr' => 'href', 'broken_url' => '/dead', 'new_url' => '/live', 'action' => 'replace']];
        $html = Transformer::applyBrokenLinkFixes('<a data-href="/dead" href="/dead">link</a>', $fixes);

        $this->assertSame('<a data-href="/dead" href="/live">link</a>', $html);
    }

    public function testValidateApiResponseRejectsWhenErrorsPresent(): void
    {
        $this->assertFalse(Transformer::validateApiResponse(['title' => 'T', 'errors' => ['boom']]));
    }

    public function testValidateApiResponseRejectsWhenNoActionableField(): void
    {
        $this->assertFalse(Transformer::validateApiResponse(['errors' => []]));
    }

    public function testValidateApiResponseAcceptsWhenTitlePresent(): void
    {
        $this->assertTrue(Transformer::validateApiResponse(['title' => 'T']));
    }

    public function testValidateApiResponseAcceptsWhenOnlyDiffsPresent(): void
    {
        $this->assertTrue(Transformer::validateApiResponse(['diffs' => [['original_text' => 'a', 'replacement_html' => 'b']]]));
    }

    public function testValidateApiResponseRejectsWhenSuggestionsNotArray(): void
    {
        $this->assertFalse(Transformer::validateApiResponse(['title' => 'T', 'suggestions' => 'nope']));
    }

    public function testValidateApiResponseRejectsWhenImagesNotArray(): void
    {
        $this->assertFalse(Transformer::validateApiResponse(['title' => 'T', 'images' => 'nope']));
    }

    public function testValidateApiResponseRejectsWhenDiffsNotArray(): void
    {
        $this->assertFalse(Transformer::validateApiResponse(['title' => 'T', 'diffs' => 'nope']));
    }

    public function testValidateApiResponseAcceptsWhenOnlyBrokenLinkFixesPresent(): void
    {
        $this->assertTrue(Transformer::validateApiResponse([
            'broken_link_fixes' => [['tag' => 'a', 'attr' => 'href', 'broken_url' => '/dead', 'new_url' => '/live']],
        ]));
    }

    public function testValidateApiResponseRejectsWhenBrokenLinkFixesNotArray(): void
    {
        $this->assertFalse(Transformer::validateApiResponse(['title' => 'T', 'broken_link_fixes' => 'nope']));
    }

    public function testValidateApiResponseRejectsWhenOnlyH1Present(): void
    {
        // h1 is intentionally not actionable on its own — matches node/python C1.
        $this->assertFalse(Transformer::validateApiResponse(['h1' => 'New Heading']));
    }

    public function testInjectInternalLinksContentAreaLinksInsideParagraphNotNav(): void
    {
        $manifest = $this->emptyManifest();
        $data = [
            'suggestions' => [['keyword' => 'widget', 'url' => '/widgets', 'id' => 1]],
            'custom_link_class' => '',
            'insert_into_content_only' => true,
        ];
        $out = Transformer::injectInternalLinks('<nav>widget</nav><p>widget</p>', $data, $manifest);

        $this->assertSame('<nav>widget</nav><p><a href="/widgets" data-seojuice-cs="1">widget</a></p>', $out);
    }

    public function testInjectInternalLinksContentAreaAllowsListAndSpanTags(): void
    {
        $manifest = $this->emptyManifest();
        $data = [
            'suggestions' => [['keyword' => 'widget', 'url' => '/widgets', 'id' => 1]],
            'custom_link_class' => '',
            'insert_into_content_only' => true,
        ];
        $out = Transformer::injectInternalLinks('<ul><li>widget</li></ul>', $data, $manifest);

        $this->assertSame('<ul><li><a href="/widgets" data-seojuice-cs="1">widget</a></li></ul>', $out);
    }

    public function testAddManifestCommentAddsCombinedMutations(): void
    {
        $manifest = ['cs' => [1, 2], 'meta' => ['title'], 'img' => 2, 'schema' => 1, 'h1' => 1];
        $html = Transformer::addManifestComment('<body></body>', $manifest);

        $this->assertSame('<body><!-- seojuice: cs=[1,2] meta=[title] img=2 schema=1 h1=1 -->' . "\n</body>", $html);
    }

    public function testAddManifestCommentIsIdempotent(): void
    {
        $manifest = ['cs' => [1], 'meta' => [], 'img' => 0, 'schema' => 0, 'h1' => 0];
        $html = Transformer::addManifestComment('<!-- seojuice: cs=[1] --><body></body>', $manifest);

        $this->assertSame('<!-- seojuice: cs=[1] --><body></body>', $html);
    }

    public function testAddManifestCommentSkipsWhenNothingChanged(): void
    {
        $manifest = $this->emptyManifest();
        $html = Transformer::addManifestComment('<body></body>', $manifest);

        $this->assertSame('<body></body>', $html);
    }

    public function testAddSsrFlagAddsScriptOnce(): void
    {
        $html = Transformer::addSsrFlag('<body></body>');

        $this->assertSame("<body><script>window.seojuiceSSR = true;</script>\n</body>", $html);
    }

    public function testAddSsrFlagIsIdempotent(): void
    {
        $existing = '<script>window.seojuiceSSR = true;</script><body></body>';
        $html = Transformer::addSsrFlag($existing);

        $this->assertSame($existing, $html);
    }
}
