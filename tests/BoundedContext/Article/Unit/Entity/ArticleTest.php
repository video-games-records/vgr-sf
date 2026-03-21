<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Article\Unit\Entity;

use App\BoundedContext\Article\Domain\Entity\Article;
use App\BoundedContext\Article\Domain\Entity\ArticleTranslation;
use App\BoundedContext\Article\Domain\Entity\Comment;
use App\BoundedContext\Article\Domain\ValueObject\ArticleStatus;
use App\BoundedContext\User\Domain\Entity\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    private Article $article;

    protected function setUp(): void
    {
        $this->article = new Article();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyCollections(): void
    {
        $article = new Article();

        $this->assertInstanceOf(Collection::class, $article->getComments());
        $this->assertCount(0, $article->getComments());
        $this->assertInstanceOf(Collection::class, $article->getTranslations());
        $this->assertCount(0, $article->getTranslations());
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->article->getId());
    }

    public function testStatusDefaultsToUnderConstruction(): void
    {
        $this->assertSame(ArticleStatus::UNDER_CONSTRUCTION, $this->article->getStatus());
    }

    public function testSetAndGetStatus(): void
    {
        $result = $this->article->setStatus(ArticleStatus::PUBLISHED);
        $this->assertSame(ArticleStatus::PUBLISHED, $this->article->getStatus());
        $this->assertSame($this->article, $result);
    }

    public function testGetArticleStatusReturnsSameAsGetStatus(): void
    {
        $this->article->setStatus(ArticleStatus::CANCELED);
        $this->assertSame($this->article->getStatus(), $this->article->getArticleStatus());
    }

    public function testNbCommentDefaultsToZero(): void
    {
        $this->assertSame(0, $this->article->getNbComment());
    }

    public function testSetAndGetNbComment(): void
    {
        $result = $this->article->setNbComment(5);
        $this->assertSame(5, $this->article->getNbComment());
        $this->assertSame($this->article, $result);
    }

    public function testViewsDefaultsToZero(): void
    {
        $this->assertSame(0, $this->article->getViews());
    }

    public function testSetAndGetViews(): void
    {
        $result = $this->article->setViews(100);
        $this->assertSame(100, $this->article->getViews());
        $this->assertSame($this->article, $result);
    }

    public function testPublishedAtDefaultsToNull(): void
    {
        $this->assertNull($this->article->getPublishedAt());
    }

    public function testSetAndGetPublishedAt(): void
    {
        $date = new DateTime('2025-01-15');
        $result = $this->article->setPublishedAt($date);
        $this->assertSame($date, $this->article->getPublishedAt());
        $this->assertSame($this->article, $result);
    }

    public function testSetPublishedAtToNull(): void
    {
        $this->article->setPublishedAt(new DateTime());
        $this->article->setPublishedAt(null);
        $this->assertNull($this->article->getPublishedAt());
    }

    public function testSetAndGetSlug(): void
    {
        $result = $this->article->setSlug('my-article-slug');
        $this->assertSame('my-article-slug', $this->article->getSlug());
        $this->assertSame($this->article, $result);
    }

    // ------------------------------------------------------------------
    // Author relation
    // ------------------------------------------------------------------

    public function testSetAndGetAuthor(): void
    {
        $user = $this->createMock(User::class);
        $result = $this->article->setAuthor($user);
        $this->assertSame($user, $this->article->getAuthor());
        $this->assertSame($this->article, $result);
    }

    // ------------------------------------------------------------------
    // Comments collection
    // ------------------------------------------------------------------

    public function testSetAndGetComments(): void
    {
        $comment = $this->createMock(Comment::class);
        $collection = new ArrayCollection([$comment]);
        $result = $this->article->setComments($collection);
        $this->assertSame($collection, $this->article->getComments());
        $this->assertSame($this->article, $result);
    }

    // ------------------------------------------------------------------
    // Translations
    // ------------------------------------------------------------------

    public function testSetAndGetTranslations(): void
    {
        $translation = $this->createMock(ArticleTranslation::class);
        $collection = new ArrayCollection(['en' => $translation]);
        $result = $this->article->setTranslations($collection);
        $this->assertSame($collection, $this->article->getTranslations());
        $this->assertSame($this->article, $result);
    }

    public function testSetTitleCreatesTranslationForLocale(): void
    {
        $this->article->setTitle('Hello World', 'en');
        $this->assertSame('Hello World', $this->article->getTitle('en'));
    }

    public function testSetTitleReturnsSelf(): void
    {
        $result = $this->article->setTitle('Hello', 'en');
        $this->assertSame($this->article, $result);
    }

    public function testGetTitleReturnsNullWhenNoTranslation(): void
    {
        $this->assertNull($this->article->getTitle('fr'));
    }

    public function testSetContentCreatesTranslationForLocale(): void
    {
        $this->article->setContent('Some content here.', 'en');
        $this->assertSame('Some content here.', $this->article->getContent('en'));
    }

    public function testSetContentReturnsSelf(): void
    {
        $result = $this->article->setContent('Content', 'en');
        $this->assertSame($this->article, $result);
    }

    public function testGetContentReturnsNullWhenNoTranslation(): void
    {
        $this->assertNull($this->article->getContent('fr'));
    }

    public function testSetTitleAndContentForSameLocaleShareTranslation(): void
    {
        $this->article->setTitle('Title EN', 'en');
        $this->article->setContent('Content EN', 'en');

        $this->assertSame('Title EN', $this->article->getTitle('en'));
        $this->assertSame('Content EN', $this->article->getContent('en'));
        $this->assertCount(1, $this->article->getTranslations());
    }

    public function testMultipleLocalesCreateSeparateTranslations(): void
    {
        $this->article->setTitle('Title EN', 'en');
        $this->article->setTitle('Titre FR', 'fr');

        $this->assertCount(2, $this->article->getTranslations());
        $this->assertSame('Title EN', $this->article->getTitle('en'));
        $this->assertSame('Titre FR', $this->article->getTitle('fr'));
    }

    // ------------------------------------------------------------------
    // Derived / utility methods
    // ------------------------------------------------------------------

    public function testGetDefaultTitleReturnsEnglishTitle(): void
    {
        $this->article->setTitle('Default Title', 'en');
        $this->assertSame('Default Title', $this->article->getDefaultTitle());
    }

    public function testGetDefaultTitleReturnsFallbackWhenNoEnglish(): void
    {
        $this->assertSame('Untitled', $this->article->getDefaultTitle());
    }

    public function testGetDefaultContentReturnsEnglishContent(): void
    {
        $this->article->setContent('Default Content', 'en');
        $this->assertSame('Default Content', $this->article->getDefaultContent());
    }

    public function testGetDefaultContentReturnsEmptyStringWhenNoEnglish(): void
    {
        $this->assertSame('', $this->article->getDefaultContent());
    }

    public function testIncrementViews(): void
    {
        $this->article->setViews(10);
        $this->article->incrementViews();
        $this->assertSame(11, $this->article->getViews());
    }

    public function testIncrementViewsFromZero(): void
    {
        $this->article->incrementViews();
        $this->assertSame(1, $this->article->getViews());
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToStringWithNoTranslation(): void
    {
        $result = (string) $this->article;
        $this->assertStringContainsString('Untitled', $result);
        $this->assertStringContainsString('[', $result);
    }

    public function testToStringWithTitle(): void
    {
        $this->article->setTitle('My Article', 'en');
        $result = (string) $this->article;
        $this->assertStringContainsString('My Article', $result);
    }

    // ------------------------------------------------------------------
    // mergeNewTranslations (no-op)
    // ------------------------------------------------------------------

    public function testMergeNewTranslationsDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();
        $this->article->mergeNewTranslations();
    }
}
