<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Article\Unit\Entity;

use App\BoundedContext\Article\Domain\Entity\Article;
use App\BoundedContext\Article\Domain\Entity\ArticleTranslation;
use PHPUnit\Framework\TestCase;

class ArticleTranslationTest extends TestCase
{
    private ArticleTranslation $translation;

    protected function setUp(): void
    {
        $this->translation = new ArticleTranslation();
    }

    // ------------------------------------------------------------------
    // Default values
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->translation->getId());
    }

    public function testTitleDefaultsToEmptyString(): void
    {
        $this->assertSame('', $this->translation->getTitle());
    }

    public function testContentDefaultsToEmptyString(): void
    {
        $this->assertSame('', $this->translation->getContent());
    }

    // ------------------------------------------------------------------
    // Locale getter / setter
    // ------------------------------------------------------------------

    public function testSetAndGetLocale(): void
    {
        $result = $this->translation->setLocale('en');
        $this->assertSame('en', $this->translation->getLocale());
        $this->assertSame($this->translation, $result);
    }

    public function testLocaleIsPublicProperty(): void
    {
        $this->translation->locale = 'fr';
        $this->assertSame('fr', $this->translation->getLocale());
    }

    // ------------------------------------------------------------------
    // Title getter / setter
    // ------------------------------------------------------------------

    public function testSetAndGetTitle(): void
    {
        $result = $this->translation->setTitle('My Article Title');
        $this->assertSame('My Article Title', $this->translation->getTitle());
        $this->assertSame($this->translation, $result);
    }

    public function testSetTitleToEmptyString(): void
    {
        $this->translation->setTitle('Something');
        $this->translation->setTitle('');
        $this->assertSame('', $this->translation->getTitle());
    }

    // ------------------------------------------------------------------
    // Content getter / setter
    // ------------------------------------------------------------------

    public function testSetAndGetContent(): void
    {
        $result = $this->translation->setContent('Article body content.');
        $this->assertSame('Article body content.', $this->translation->getContent());
        $this->assertSame($this->translation, $result);
    }

    public function testSetContentToEmptyString(): void
    {
        $this->translation->setContent('Some content');
        $this->translation->setContent('');
        $this->assertSame('', $this->translation->getContent());
    }

    // ------------------------------------------------------------------
    // Translatable relation
    // ------------------------------------------------------------------

    public function testSetAndGetTranslatable(): void
    {
        $article = $this->createMock(Article::class);
        $result = $this->translation->setTranslatable($article);
        $this->assertSame($article, $this->translation->getTranslatable());
        $this->assertSame($this->translation, $result);
    }

    // ------------------------------------------------------------------
    // isEmpty
    // ------------------------------------------------------------------

    public function testIsEmptyReturnsTrueWhenBothTitleAndContentAreEmpty(): void
    {
        $this->assertTrue($this->translation->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenTitleIsSet(): void
    {
        $this->translation->setTitle('Some title');
        $this->assertFalse($this->translation->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenContentIsSet(): void
    {
        $this->translation->setContent('Some content');
        $this->assertFalse($this->translation->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenBothAreSet(): void
    {
        $this->translation->setTitle('Title');
        $this->translation->setContent('Content');
        $this->assertFalse($this->translation->isEmpty());
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToStringReturnsTitle(): void
    {
        $this->translation->setTitle('Hello');
        $this->assertSame('Hello', (string) $this->translation);
    }

    public function testToStringReturnsEmptyStringWhenNoTitle(): void
    {
        $this->assertSame('', (string) $this->translation);
    }
}
