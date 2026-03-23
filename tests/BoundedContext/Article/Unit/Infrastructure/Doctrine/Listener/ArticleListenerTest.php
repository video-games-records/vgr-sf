<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Article\Unit\Infrastructure\Doctrine\Listener;

use App\BoundedContext\Article\Domain\Entity\Article;
use App\BoundedContext\Article\Domain\ValueObject\ArticleStatus;
use App\BoundedContext\Article\Infrastructure\Doctrine\Listener\ArticleListener;
use App\BoundedContext\Article\Presentation\Web\Controller\TopNewsController;
use DateTime;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;
use Symfony\Contracts\Cache\CacheInterface;

#[AllowMockObjectsWithoutExpectations]
class ArticleListenerTest extends TestCase
{
    private SluggerInterface&Stub $slugger;
    private CacheInterface&MockObject $cache;
    private ArticleListener $listener;

    protected function setUp(): void
    {
        $this->slugger = $this->createStub(SluggerInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->listener = new ArticleListener($this->slugger, $this->cache);

        $this->slugger->method('slug')->willReturnCallback(
            fn (string $str) => new UnicodeString($str)
        );
    }

    // ------------------------------------------------------------------
    // prePersist
    // ------------------------------------------------------------------

    public function testPrePersistSetsPublishedAtForPublishedArticle(): void
    {
        $article = $this->buildArticle(ArticleStatus::PUBLISHED, null);

        $this->listener->prePersist($article);

        $this->assertInstanceOf(DateTime::class, $article->getPublishedAt());
    }

    public function testPrePersistDeletesCacheForPublishedArticle(): void
    {
        $article = $this->buildArticle(ArticleStatus::PUBLISHED, null);

        $this->cache->expects($this->once())
            ->method('delete')
            ->with(TopNewsController::CACHE_KEY);

        $this->listener->prePersist($article);
    }

    public function testPrePersistDoesNotSetPublishedAtForDraftArticle(): void
    {
        $article = $this->buildArticle(ArticleStatus::UNDER_CONSTRUCTION, null);

        $this->listener->prePersist($article);

        $this->assertNull($article->getPublishedAt());
    }

    public function testPrePersistDoesNotDeleteCacheForDraftArticle(): void
    {
        $article = $this->buildArticle(ArticleStatus::UNDER_CONSTRUCTION, null);

        $this->cache->expects($this->never())->method('delete');

        $this->listener->prePersist($article);
    }

    public function testPrePersistUpdatesSlug(): void
    {
        $article = $this->buildArticle(ArticleStatus::UNDER_CONSTRUCTION, null);

        $this->listener->prePersist($article);

        $this->assertSame('my article title', $article->getSlug());
    }

    // ------------------------------------------------------------------
    // preUpdate
    // ------------------------------------------------------------------

    public function testPreUpdateSetsPublishedAtWhenPublishedAndNoPublishedAt(): void
    {
        $article = $this->buildArticle(ArticleStatus::PUBLISHED, null);

        $this->listener->preUpdate($article);

        $this->assertInstanceOf(DateTime::class, $article->getPublishedAt());
    }

    public function testPreUpdateDoesNotOverwriteExistingPublishedAt(): void
    {
        $existingDate = new DateTime('2024-01-01');
        $article = $this->buildArticle(ArticleStatus::PUBLISHED, $existingDate);

        $this->listener->preUpdate($article);

        $this->assertSame($existingDate, $article->getPublishedAt());
    }

    public function testPreUpdateDeletesCacheForPublishedArticle(): void
    {
        $article = $this->buildArticle(ArticleStatus::PUBLISHED, new DateTime());

        $this->cache->expects($this->once())
            ->method('delete')
            ->with(TopNewsController::CACHE_KEY);

        $this->listener->preUpdate($article);
    }

    public function testPreUpdateDoesNotDeleteCacheForDraftArticle(): void
    {
        $article = $this->buildArticle(ArticleStatus::UNDER_CONSTRUCTION, null);

        $this->cache->expects($this->never())->method('delete');

        $this->listener->preUpdate($article);
    }

    public function testPreUpdateUpdatesSlug(): void
    {
        $article = $this->buildArticle(ArticleStatus::UNDER_CONSTRUCTION, null);

        $this->listener->preUpdate($article);

        $this->assertSame('my article title', $article->getSlug());
    }

    public function testPreUpdateDoesNotSetPublishedAtForCanceledArticle(): void
    {
        $article = $this->buildArticle(ArticleStatus::CANCELED, null);

        $this->listener->preUpdate($article);

        $this->assertNull($article->getPublishedAt());
    }

    // ------------------------------------------------------------------
    // Helper
    // ------------------------------------------------------------------

    private function buildArticle(ArticleStatus $status, ?DateTime $publishedAt): Article
    {
        $article = new Article();
        $article->setStatus($status);
        $article->setPublishedAt($publishedAt);
        $article->setTitle('My Article Title', 'en');
        $article->setSlug('old-slug');
        return $article;
    }
}
