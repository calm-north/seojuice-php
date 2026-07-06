<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Injection;

use PHPUnit\Framework\TestCase;
use SEOJuice\Injection\SeoInjector;

final class SeoInjectorTest extends TestCase
{
    private SeoInjector $injector;

    protected function setUp(): void
    {
        $this->injector = new SeoInjector();
    }

    private function makeHtml(string $headContent = '', string $bodyContent = 'Body content long enough to survive the fail-open length-ratio check comfortably.'): string
    {
        return "<html><head>{$headContent}</head><body>{$bodyContent}</body></html>";
    }

    public function testInjectReturnsUnchangedHtmlWhenValidationFails(): void
    {
        $html = $this->makeHtml();

        $result = $this->injector->inject($html, ['errors' => ['boom']]);

        $this->assertSame($html, $result);
    }

    public function testInjectReturnsUnchangedHtmlWhenNothingActionable(): void
    {
        $html = $this->makeHtml();

        $result = $this->injector->inject($html, []);

        $this->assertSame($html, $result);
    }

    public function testInjectAddsTitleAndMarksManifest(): void
    {
        $html = $this->makeHtml();

        $result = $this->injector->inject($html, ['title' => 'My Page Title']);

        $this->assertStringContainsString('<title data-seojuice="title">My Page Title</title>', $result);
        $this->assertStringContainsString('<!-- seojuice: meta=[title] -->', $result);
    }

    public function testInjectEscapesSpecialCharactersInMeta(): void
    {
        $html = $this->makeHtml();

        $result = $this->injector->inject($html, ['title' => 'Page "Title" & <More>']);

        $this->assertStringContainsString('Page &quot;Title&quot; &amp; &lt;More&gt;', $result);
    }

    public function testInjectAppliesImageAlt(): void
    {
        $html = $this->makeHtml('', '<img src="/logo.png"> Body content long enough to survive fail-open.');

        $result = $this->injector->inject($html, [
            'images' => [['url' => '/logo.png', 'alt_text' => 'Company Logo']],
        ]);

        $this->assertStringContainsString('alt="Company Logo"', $result);
    }

    public function testInjectAddsInternalLinkWithCsMarker(): void
    {
        $html = $this->makeHtml('', '<p>Read our widget guide for details on widgets today.</p>');

        $result = $this->injector->inject($html, [
            'suggestions' => [['keyword' => 'widget', 'url' => '/widgets', 'id' => 7]],
        ]);

        $this->assertStringContainsString('<a href="/widgets" data-seojuice-cs="7">widget</a>', $result);
        $this->assertStringContainsString('cs=[7]', $result);
    }

    public function testInjectAppliesContentDiff(): void
    {
        $html = $this->makeHtml('', '<div><p>old copy here that is long enough to pass fail-open</p></div>');

        $result = $this->injector->inject($html, [
            'diffs' => [[
                'id' => 9,
                'original_text' => '<p>old copy here that is long enough to pass fail-open</p>',
                'replacement_html' => '<p>new premium copy here that is long enough to pass fail-open</p>',
            ]],
        ]);

        $this->assertStringContainsString('new premium copy', $result);
        $this->assertStringContainsString('data-seojuice-cs="9"', $result);
    }

    public function testInjectReplacesH1(): void
    {
        $html = $this->makeHtml('', '<h1>Old Heading</h1><p>Body content long enough to survive the fail-open ratio check.</p>');

        $result = $this->injector->inject($html, ['h1' => 'New Heading']);

        $this->assertStringContainsString('<h1 data-seojuice="h1">New Heading</h1>', $result);
    }

    public function testInjectAppliesBrokenLinkFix(): void
    {
        $html = $this->makeHtml('', '<p>See our <a href="/dead">guide</a> for more details on this topic.</p>');

        $result = $this->injector->inject($html, [
            'broken_link_fixes' => [[
                'tag' => 'a', 'attr' => 'href', 'broken_url' => '/dead', 'new_url' => '/live',
            ]],
        ]);

        $this->assertStringContainsString('href="/live"', $result);
        $this->assertStringNotContainsString('href="/dead"', $result);
    }

    public function testInjectAlwaysAddsSsrFlagWhenProcessed(): void
    {
        $html = $this->makeHtml();

        $result = $this->injector->inject($html, ['title' => 'T']);

        $this->assertStringContainsString('window.seojuiceSSR = true;', $result);
    }

    public function testInjectFailsOpenWhenNoBodyTag(): void
    {
        $html = '<html><head></head><p>No body wrapper here at all</p></html>';

        $result = $this->injector->inject($html, ['title' => 'Injected Title Should Not Appear']);

        $this->assertSame($html, $result);
    }

    public function testInjectChainsAllTransformationsTogether(): void
    {
        $html = '<html><head></head><body><img src="/logo.png"><p>Read our widget guide for more on widgets today.</p></body></html>';

        $result = $this->injector->inject($html, [
            'title' => 'Home Page',
            'meta_description' => 'Welcome to our site',
            'og_title' => 'OG Home',
            'og_image' => 'https://example.com/og.jpg',
            'structured_data' => json_encode(json_encode(['@type' => 'WebSite', 'name' => 'Our Site'])),
            'images' => [['url' => '/logo.png', 'alt_text' => 'Company Logo']],
            'suggestions' => [['keyword' => 'widget', 'url' => '/widgets', 'id' => 1]],
        ]);

        $this->assertStringContainsString('<title data-seojuice="title">Home Page</title>', $result);
        $this->assertStringContainsString('content="Welcome to our site"', $result);
        $this->assertStringContainsString('og:title', $result);
        $this->assertStringContainsString('og:image', $result);
        $this->assertStringContainsString('application/ld+json', $result);
        $this->assertStringContainsString('"@type":"WebSite"', $result);
        $this->assertStringContainsString('alt="Company Logo"', $result);
        $this->assertStringContainsString('<a href="/widgets" data-seojuice-cs="1">widget</a>', $result);
        $this->assertStringContainsString('window.seojuiceSSR = true;', $result);
    }

    public function testInjectIsCaseInsensitiveForHeadTag(): void
    {
        $html = '<html><HEAD></HEAD><body>Body content long enough to survive the fail-open ratio check.</body></html>';

        $result = $this->injector->inject($html, ['title' => 'Test Title']);

        $this->assertStringContainsString('Test Title', $result);
    }
}
