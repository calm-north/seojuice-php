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
}
