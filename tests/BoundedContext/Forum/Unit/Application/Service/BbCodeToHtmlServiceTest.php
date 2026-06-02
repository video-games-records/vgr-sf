<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Forum\Unit\Application\Service;

use App\BoundedContext\Forum\Application\Service\BbCodeToHtmlService;
use PHPUnit\Framework\TestCase;

class BbCodeToHtmlServiceTest extends TestCase
{
    private BbCodeToHtmlService $service;

    protected function setUp(): void
    {
        $this->service = new BbCodeToHtmlService();
    }

    // ------------------------------------------------------------------
    // hasBbCode
    // ------------------------------------------------------------------

    public function testHasBbCodeReturnsFalseForPlainText(): void
    {
        $this->assertFalse($this->service->hasBbCode('Hello world'));
    }

    public function testHasBbCodeReturnsTrueForBoldTag(): void
    {
        $this->assertTrue($this->service->hasBbCode('[b]bold[/b]'));
    }

    public function testHasBbCodeReturnsTrueForUrlTag(): void
    {
        $this->assertTrue($this->service->hasBbCode('[url]http://example.com[/url]'));
    }

    public function testHasBbCodeReturnsTrueForImgTag(): void
    {
        $this->assertTrue($this->service->hasBbCode('[img]http://example.com/img.png[/img]'));
    }

    public function testHasBbCodeReturnsFalseForHtmlOnly(): void
    {
        $this->assertFalse($this->service->hasBbCode('<strong>bold</strong>'));
    }

    // ------------------------------------------------------------------
    // convert — plain text passthrough
    // ------------------------------------------------------------------

    public function testConvertReturnsPlainTextUnchanged(): void
    {
        $text = 'No BBCode here.';
        $this->assertSame($text, $this->service->convert($text));
    }

    // ------------------------------------------------------------------
    // Basic tags
    // ------------------------------------------------------------------

    public function testConvertBoldTag(): void
    {
        $result = $this->service->convert('[b]hello[/b]');
        $this->assertStringContainsString('<strong>hello</strong>', $result);
    }

    public function testConvertItalicTag(): void
    {
        $result = $this->service->convert('[i]hello[/i]');
        $this->assertStringContainsString('<em>hello</em>', $result);
    }

    public function testConvertUnderlineTag(): void
    {
        $result = $this->service->convert('[u]hello[/u]');
        $this->assertStringContainsString('<u>hello</u>', $result);
    }

    public function testConvertStrikethroughTag(): void
    {
        $result = $this->service->convert('[s]hello[/s]');
        $this->assertStringContainsString('<s>hello</s>', $result);
    }

    public function testConvertCenterTag(): void
    {
        $result = $this->service->convert('[center]hello[/center]');
        $this->assertStringContainsString('<div class="text-center">hello</div>', $result);
    }

    public function testConvertColorTagWithName(): void
    {
        $result = $this->service->convert('[color=red]hello[/color]');
        $this->assertStringContainsString('<span style="color:red">hello</span>', $result);
    }

    public function testConvertColorTagWithHex(): void
    {
        $result = $this->service->convert('[color=#ff0000]hello[/color]');
        $this->assertStringContainsString('<span style="color:#ff0000">hello</span>', $result);
    }

    // ------------------------------------------------------------------
    // Links
    // ------------------------------------------------------------------

    public function testConvertUrlTagWithText(): void
    {
        $result = $this->service->convert('[url=http://example.com]Click here[/url]');
        $this->assertStringContainsString('<a href="http://example.com" rel="nofollow">Click here</a>', $result);
    }

    public function testConvertUrlTagWithoutText(): void
    {
        $result = $this->service->convert('[url]http://example.com[/url]');
        $this->assertStringContainsString('<a href="http://example.com" rel="nofollow">http://example.com</a>', $result);
    }

    // ------------------------------------------------------------------
    // Images
    // ------------------------------------------------------------------

    public function testConvertImgTag(): void
    {
        $result = $this->service->convert('[img]http://example.com/photo.jpg[/img]');
        $this->assertStringContainsString('<img src="http://example.com/photo.jpg" alt="" class="img-fluid">', $result);
    }

    // ------------------------------------------------------------------
    // Quotes
    // ------------------------------------------------------------------

    public function testConvertQuoteTagWithAuthor(): void
    {
        $result = $this->service->convert('[quote=Alice]Some text[/quote]');
        $this->assertStringContainsString('<blockquote', $result);
        $this->assertStringContainsString('Alice', $result);
        $this->assertStringContainsString('Some text', $result);
    }

    public function testConvertQuoteTagWithoutAuthor(): void
    {
        $result = $this->service->convert('[quote]Some text[/quote]');
        $this->assertStringContainsString('<blockquote', $result);
        $this->assertStringContainsString('Some text', $result);
    }

    // ------------------------------------------------------------------
    // Code
    // ------------------------------------------------------------------

    public function testConvertCodeTag(): void
    {
        $result = $this->service->convert('[code]echo "hello";[/code]');
        $this->assertStringContainsString('<pre><code>', $result);
        $this->assertStringContainsString('echo "hello";', $result);
        $this->assertStringContainsString('</code></pre>', $result);
    }

    // ------------------------------------------------------------------
    // Lists
    // ------------------------------------------------------------------

    public function testConvertUnorderedList(): void
    {
        $result = $this->service->convert('[list][*]Item 1[*]Item 2[/list]');
        $this->assertStringContainsString('<ul>', $result);
        $this->assertStringContainsString('<li>', $result);
        $this->assertStringContainsString('Item 1', $result);
        $this->assertStringContainsString('Item 2', $result);
        $this->assertStringContainsString('</ul>', $result);
    }

    public function testConvertOrderedList(): void
    {
        $result = $this->service->convert('[list=1][*]First[*]Second[/list]');
        $this->assertStringContainsString('<ol>', $result);
        $this->assertStringContainsString('<li>', $result);
        $this->assertStringContainsString('First', $result);
        $this->assertStringContainsString('</ol>', $result);
    }

    // ------------------------------------------------------------------
    // Newlines
    // ------------------------------------------------------------------

    public function testConvertNewlinesWhenNoBbBlock(): void
    {
        $result = $this->service->convert("[b]line1[/b]\nline2");
        $this->assertStringContainsString('<br />', $result);
    }

    public function testNoNewlineConversionWhenHtmlBlockPresent(): void
    {
        $result = $this->service->convert("[quote]Some text[/quote]\nother");
        $this->assertStringNotContainsString('<br />', $result);
    }

    // ------------------------------------------------------------------
    // Case-insensitive tags
    // ------------------------------------------------------------------

    public function testConvertBoldTagCaseInsensitive(): void
    {
        $result = $this->service->convert('[B]hello[/B]');
        $this->assertStringContainsString('<strong>hello</strong>', $result);
    }
}
