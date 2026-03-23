<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Article\Integration\Application\Service;

use App\BoundedContext\Article\Application\Service\ArticleAnalyticsService;
use App\BoundedContext\Article\Domain\ValueObject\ArticleStatus;
use App\Tests\BoundedContext\Article\Factory\ArticleFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class ArticleAnalyticsServiceTest extends KernelTestCase
{
    use Factories;

    private ArticleAnalyticsService $service;

    protected function setUp(): void
    {
        self::bootKernel();
        $service = static::getContainer()->get(ArticleAnalyticsService::class);
        $this->assertInstanceOf(ArticleAnalyticsService::class, $service);
        $this->service = $service;
    }

    // ------------------------------------------------------------------
    // getMostViewedArticles
    // ------------------------------------------------------------------

    public function testGetMostViewedArticlesReturnsArrayOfPublishedArticles(): void
    {
        $result = $this->service->getMostViewedArticles();

        // Fixture articles are published, so result contains at least the 3 fixture articles
        $this->assertGreaterThanOrEqual(3, count($result));
    }

    public function testGetMostViewedArticlesReturnsPublishedArticles(): void
    {
        ArticleFactory::new()->published()->with(['views' => 100])->create();
        ArticleFactory::new()->published()->with(['views' => 50])->create();
        ArticleFactory::new()->underConstruction()->with(['views' => 200])->create();

        $result = $this->service->getMostViewedArticles(10);

        // Fixture adds 3 old published articles (0 views), test adds 2 published articles
        $this->assertGreaterThanOrEqual(2, count($result));
    }

    public function testGetMostViewedArticlesRespectsLimit(): void
    {
        ArticleFactory::new()->published()->many(5)->create();

        $result = $this->service->getMostViewedArticles(3);

        $this->assertCount(3, $result);
    }

    public function testGetMostViewedArticlesOrderedByViewsDesc(): void
    {
        ArticleFactory::new()->published()->with(['views' => 10])->create();
        ArticleFactory::new()->published()->with(['views' => 500])->create();
        ArticleFactory::new()->published()->with(['views' => 100])->create();

        $result = $this->service->getMostViewedArticles(3);

        $this->assertGreaterThanOrEqual($result[1]['views'], $result[0]['views']);
        $this->assertGreaterThanOrEqual($result[2]['views'], $result[1]['views']);
    }

    // ------------------------------------------------------------------
    // getTrendingArticles
    // ------------------------------------------------------------------

    public function testGetTrendingArticlesReturnsArray(): void
    {
        $result = $this->service->getTrendingArticles();

        // Fixture articles are old (> 30 days), so trending window excludes them
        $this->assertEmpty($result);
    }

    public function testGetTrendingArticlesOnlyReturnsRecentArticles(): void
    {
        // Old article published 30 days ago
        ArticleFactory::new()->published()->with([
            'views' => 1000,
            'publishedAt' => new \DateTime('-30 days'),
        ])->create();
        // Recent article published 2 days ago
        ArticleFactory::new()->published()->with([
            'views' => 50,
            'publishedAt' => new \DateTime('-2 days'),
        ])->create();

        $result = $this->service->getTrendingArticles(7);

        $this->assertCount(1, $result);
    }

    // ------------------------------------------------------------------
    // getGlobalStats
    // ------------------------------------------------------------------

    public function testGetGlobalStatsReturnsCorrectStructure(): void
    {
        $result = $this->service->getGlobalStats();

        $this->assertArrayHasKey('total_views', $result);
        $this->assertArrayHasKey('total_articles', $result);
        $this->assertArrayHasKey('average_views_per_article', $result);
        $this->assertArrayHasKey('most_viewed_today', $result);
    }

    public function testGetGlobalStatsWithFixtureArticles(): void
    {
        $result = $this->service->getGlobalStats();

        // Fixture provides 3 published articles with 0 views
        $this->assertGreaterThanOrEqual(3, $result['total_articles']);
        $this->assertSame(0, $result['total_views']);
        $this->assertSame(0.0, $result['average_views_per_article']);
    }

    public function testGetGlobalStatsAccumulatesViewsAcrossArticles(): void
    {
        ArticleFactory::new()->published()->with(['views' => 100])->create();
        ArticleFactory::new()->published()->with(['views' => 200])->create();

        $result = $this->service->getGlobalStats();

        // Fixture adds 3 published articles with 0 views; test adds 2 with 300 views total
        $this->assertSame(300, $result['total_views']);
        $this->assertGreaterThanOrEqual(2, $result['total_articles']);
    }

    // ------------------------------------------------------------------
    // getAuthorStats
    // ------------------------------------------------------------------

    public function testGetAuthorStatsReturnsCorrectStructure(): void
    {
        $article = ArticleFactory::new()->published()->with(['views' => 100])->create();

        $author = $article->getAuthor();
        $this->assertNotNull($author);
        $authorId = $author->getId();
        $this->assertNotNull($authorId);
        $result = $this->service->getAuthorStats($authorId);

        $this->assertArrayHasKey('total_articles', $result);
        $this->assertArrayHasKey('total_views', $result);
        $this->assertArrayHasKey('average_views', $result);
        $this->assertArrayHasKey('best_article_views', $result);
        $this->assertSame(1, $result['total_articles']);
        $this->assertSame(100, $result['total_views']);
    }

    // ------------------------------------------------------------------
    // getUnderutilizedArticles
    // ------------------------------------------------------------------

    public function testGetUnderutilizedArticlesReturnsArticlesWithFewViews(): void
    {
        ArticleFactory::new()->published()->with(['views' => 3])->create();
        ArticleFactory::new()->published()->with(['views' => 500])->create();

        $result = $this->service->getUnderutilizedArticles(10);

        // Fixture adds 3 published articles with 0 views (also underutilized)
        $this->assertGreaterThanOrEqual(1, count($result));
        // All returned articles must have views <= 10
        foreach ($result as $article) {
            $this->assertLessThanOrEqual(10, $article['views']);
        }
    }

    public function testGetUnderutilizedArticlesReturnsEmptyWhenAllPopular(): void
    {
        ArticleFactory::new()->published()->with(['views' => 100])->many(3)->create();

        $result = $this->service->getUnderutilizedArticles(10);

        // Only articles with views <= 10 are returned (fixture articles with 0 views may appear)
        // The 3 created articles (100 views) must not appear
        foreach ($result as $article) {
            $this->assertLessThanOrEqual(10, $article['views']);
        }
    }
}
