<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Injection;

use PHPUnit\Framework\TestCase;
use SEOJuice\Injection\SeoInjector;
use SEOJuice\Injection\Suggestions;

final class SeoInjectorTest extends TestCase
{
    private SeoInjector $injector;

    protected function setUp(): void
    {
        $this->injector = new SeoInjector();
    }

    private function makeHtml(string $headContent = '', string $bodyContent = ''): string
    {
        return "<html><head>{$headContent}</head><body>{$bodyContent}</body></html>";
    }

    private function emptySuggestions(): Suggestions
    {
        return new Suggestions(
            links: [],
            images: [],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );
    }

    // --- inject() returns unchanged HTML for empty suggestions ---

    public function testInjectReturnsUnchangedHtmlForEmptySuggestions(): void
    {
        $html = $this->makeHtml('', '<p>Hello</p>');
        $suggestions = $this->emptySuggestions();

        $result = $this->injector->inject($html, $suggestions);

        $this->assertSame($html, $result);
    }

    // --- injectMetaTags ---

    public function testInjectMetaTagsInjectsTitleBeforeCloseHead(): void
    {
        $html = $this->makeHtml();
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: ['title' => 'My Page Title'],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString('<title>My Page Title</title>', $result);
        // Title should appear before </head>
        $titlePos = strpos($result, '<title>');
        $headClosePos = strpos($result, '</head>');
        $this->assertNotFalse($titlePos);
        $this->assertNotFalse($headClosePos);
        $this->assertLessThan($headClosePos, $titlePos);
    }

    public function testInjectMetaTagsInjectsDescriptionMetaTag(): void
    {
        $html = $this->makeHtml();
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: ['description' => 'A great page about SEO'],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString(
            '<meta name="description" content="A great page about SEO">',
            $result,
        );
    }

    public function testInjectMetaTagsInjectsCanonicalLink(): void
    {
        $html = $this->makeHtml();
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: ['canonical' => 'https://example.com/page'],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString(
            '<link rel="canonical" href="https://example.com/page">',
            $result,
        );
    }

    public function testInjectMetaTagsInjectsRobotsMeta(): void
    {
        $html = $this->makeHtml();
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: ['robots' => 'noindex, nofollow'],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString(
            '<meta name="robots" content="noindex, nofollow">',
            $result,
        );
    }

    public function testInjectMetaTagsEscapesSpecialCharacters(): void
    {
        $html = $this->makeHtml();
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: [
                'title' => 'Page "Title" & <More>',
                'description' => 'Description with "quotes" & <tags>',
            ],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString(
            '<title>Page &quot;Title&quot; &amp; &lt;More&gt;</title>',
            $result,
        );
        $this->assertStringContainsString(
            'content="Description with &quot;quotes&quot; &amp; &lt;tags&gt;"',
            $result,
        );
    }

    public function testInjectMetaTagsSkipsEmptyTitle(): void
    {
        $html = $this->makeHtml();
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: ['title' => '', 'description' => 'Valid description'],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringNotContainsString('<title>', $result);
        $this->assertStringContainsString('content="Valid description"', $result);
    }

    public function testInjectMetaTagsInjectsAllTagsTogether(): void
    {
        $html = $this->makeHtml();
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: [
                'title' => 'Full Title',
                'description' => 'Full Description',
                'canonical' => 'https://example.com/',
                'robots' => 'index, follow',
            ],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString('<title>Full Title</title>', $result);
        $this->assertStringContainsString('content="Full Description"', $result);
        $this->assertStringContainsString('href="https://example.com/"', $result);
        $this->assertStringContainsString('content="index, follow"', $result);
    }

    // --- injectOgTags ---

    public function testInjectOgTagsInjectsOgTitleDescriptionAndImage(): void
    {
        $html = $this->makeHtml();
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [
                'title' => 'OG Title',
                'description' => 'OG Description',
                'image' => 'https://example.com/og.jpg',
            ],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString(
            '<meta property="og:title" content="OG Title">',
            $result,
        );
        $this->assertStringContainsString(
            '<meta property="og:description" content="OG Description">',
            $result,
        );
        $this->assertStringContainsString(
            '<meta property="og:image" content="https://example.com/og.jpg">',
            $result,
        );
    }

    public function testInjectOgTagsSkipsNullValues(): void
    {
        $html = $this->makeHtml();
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [
                'title' => 'OG Title',
                'description' => null,
                'image' => '',
            ],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString('og:title', $result);
        $this->assertStringNotContainsString('og:description', $result);
        $this->assertStringNotContainsString('og:image', $result);
    }

    public function testInjectOgTagsEscapesValues(): void
    {
        $html = $this->makeHtml();
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [
                'title' => 'Title "with" <special> & chars',
            ],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString(
            'content="Title &quot;with&quot; &lt;special&gt; &amp; chars"',
            $result,
        );
    }

    public function testInjectOgTagsDoesNothingForEmptyOgTags(): void
    {
        $html = $this->makeHtml('', '<p>Content</p>');
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: ['title' => 'Some Title'],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringNotContainsString('og:', $result);
    }

    // --- injectStructuredData ---

    public function testInjectStructuredDataInjectsJsonLdScriptTags(): void
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Test Corp',
            'url' => 'https://example.com',
        ];

        $html = $this->makeHtml();
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: [],
            structuredData: [$schema],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString('<script type="application/ld+json">', $result);
        $this->assertStringContainsString('"@type":"Organization"', $result);
        $this->assertStringContainsString('"name":"Test Corp"', $result);
    }

    public function testInjectStructuredDataInjectsMultipleSchemas(): void
    {
        $schemas = [
            ['@type' => 'Organization', 'name' => 'Corp A'],
            ['@type' => 'WebSite', 'name' => 'Site A'],
        ];

        $html = $this->makeHtml();
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: [],
            structuredData: $schemas,
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertSame(2, substr_count($result, '<script type="application/ld+json">'));
        $this->assertStringContainsString('"@type":"Organization"', $result);
        $this->assertStringContainsString('"@type":"WebSite"', $result);
    }

    public function testInjectStructuredDataPreservesUrlSlashes(): void
    {
        $schema = [
            '@context' => 'https://schema.org',
            'url' => 'https://example.com/page/subpage',
        ];

        $html = $this->makeHtml();
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: [],
            structuredData: [$schema],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        // JSON_UNESCAPED_SLASHES ensures URLs are not escaped
        $this->assertStringContainsString('https://example.com/page/subpage', $result);
        $this->assertStringNotContainsString('https:\\/\\/example.com', $result);
    }

    public function testInjectStructuredDataPlacedBeforeCloseHead(): void
    {
        $schema = ['@type' => 'WebSite'];

        $html = $this->makeHtml();
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: [],
            structuredData: [$schema],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $scriptPos = strpos($result, '<script type="application/ld+json">');
        $headClosePos = strpos($result, '</head>');
        $this->assertNotFalse($scriptPos);
        $this->assertNotFalse($headClosePos);
        $this->assertLessThan($headClosePos, $scriptPos);
    }

    // --- applyImageAlts ---

    public function testApplyImageAltsAddsAltToImagesWithoutAltAttribute(): void
    {
        $html = $this->makeHtml(
            '',
            '<img src="/img/photo.jpg"> <img src="/img/other.jpg">',
        );
        $suggestions = new Suggestions(
            links: [],
            images: [
                ['src' => '/img/photo.jpg', 'alt' => 'A beautiful photo'],
            ],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString('alt="A beautiful photo"', $result);
        // The other image should not have an alt added
        $this->assertStringNotContainsString('alt="A beautiful photo"', substr(
            $result,
            (int) strpos($result, 'other.jpg'),
        ));
    }

    public function testApplyImageAltsReplacesEmptyAltAttributes(): void
    {
        $html = $this->makeHtml(
            '',
            '<img src="/img/banner.png" alt="">',
        );
        $suggestions = new Suggestions(
            links: [],
            images: [
                ['src' => '/img/banner.png', 'alt' => 'Website banner'],
            ],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString('alt="Website banner"', $result);
        $this->assertStringNotContainsString('alt=""', $result);
    }

    public function testApplyImageAltsSkipsImagesWithNullAlt(): void
    {
        $html = $this->makeHtml('', '<img src="/img/test.jpg">');
        $suggestions = new Suggestions(
            links: [],
            images: [
                ['src' => '/img/test.jpg', 'alt' => null],
            ],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        // Should not modify the image when alt is null
        $this->assertStringContainsString('<img src="/img/test.jpg">', $result);
    }

    public function testApplyImageAltsSkipsImagesWithEmptyAltSuggestion(): void
    {
        $html = $this->makeHtml('', '<img src="/img/test.jpg">');
        $suggestions = new Suggestions(
            links: [],
            images: [
                ['src' => '/img/test.jpg', 'alt' => ''],
            ],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        // Should not add empty alt
        $this->assertStringNotContainsString('alt=""', $result);
    }

    public function testApplyImageAltsEscapesSpecialCharacters(): void
    {
        $html = $this->makeHtml('', '<img src="/img/test.jpg">');
        $suggestions = new Suggestions(
            links: [],
            images: [
                ['src' => '/img/test.jpg', 'alt' => 'Photo of "cats" & <dogs>'],
            ],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString(
            'alt="Photo of &quot;cats&quot; &amp; &lt;dogs&gt;"',
            $result,
        );
    }

    public function testApplyImageAltsHandlesMultipleImages(): void
    {
        $html = $this->makeHtml(
            '',
            '<img src="/a.jpg"> <img src="/b.jpg" alt="">',
        );
        $suggestions = new Suggestions(
            links: [],
            images: [
                ['src' => '/a.jpg', 'alt' => 'Image A'],
                ['src' => '/b.jpg', 'alt' => 'Image B'],
            ],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString('alt="Image A"', $result);
        $this->assertStringContainsString('alt="Image B"', $result);
    }

    // --- Full inject() chains all transformations ---

    public function testFullInjectChainsAllTransformations(): void
    {
        $html = '<html><head></head><body><img src="/logo.png"></body></html>';
        $suggestions = new Suggestions(
            links: [['href' => '/about']],
            images: [
                ['src' => '/logo.png', 'alt' => 'Company Logo'],
            ],
            metaTags: [
                'title' => 'Home Page',
                'description' => 'Welcome to our site',
            ],
            structuredData: [
                ['@type' => 'WebSite', 'name' => 'Our Site'],
            ],
            accessibilityFixes: [
                ['type' => 'aria-label'],
            ],
            ogTags: [
                'title' => 'OG Home',
                'image' => 'https://example.com/og.jpg',
            ],
        );

        $result = $this->injector->inject($html, $suggestions);

        // Meta tags injected
        $this->assertStringContainsString('<title>Home Page</title>', $result);
        $this->assertStringContainsString('content="Welcome to our site"', $result);

        // OG tags injected
        $this->assertStringContainsString('og:title', $result);
        $this->assertStringContainsString('og:image', $result);

        // Structured data injected
        $this->assertStringContainsString('application/ld+json', $result);
        $this->assertStringContainsString('"@type":"WebSite"', $result);

        // Image alt applied
        $this->assertStringContainsString('alt="Company Logo"', $result);
    }

    public function testInjectIsCaseInsensitiveForHeadTag(): void
    {
        $html = '<html><HEAD></HEAD><body></body></html>';
        $suggestions = new Suggestions(
            links: [],
            images: [],
            metaTags: ['title' => 'Test Title'],
            structuredData: [],
            accessibilityFixes: [],
            ogTags: [],
        );

        $result = $this->injector->inject($html, $suggestions);

        $this->assertStringContainsString('<title>Test Title</title>', $result);
    }

    public function testInjectWithOnlyEmptyMetaTagsReturnsSameHtml(): void
    {
        $html = $this->makeHtml('', '<p>Content</p>');
        $suggestions = new Suggestions(
            links: [['href' => '/link']],
            images: [],
            metaTags: [],
            structuredData: [],
            accessibilityFixes: [['type' => 'fix']],
            ogTags: [],
        );

        // Note: links and accessibilityFixes are not injected by inject() directly,
        // only metaTags, ogTags, structuredData, and images are.
        // Since metaTags, ogTags, structuredData are empty, and images is empty,
        // the HTML should be unchanged (the non-empty check on isEmpty uses links/accessibilityFixes,
        // so inject() IS called, but each sub-method returns html unchanged).
        $result = $this->injector->inject($html, $suggestions);

        $this->assertSame($html, $result);
    }
}
