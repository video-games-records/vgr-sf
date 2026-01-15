<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Application\Service;

use App\BoundedContext\Article\Domain\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;

class ArticleAnalyticsService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function getMostViewedArticles(int $limit = 10): array
    {
        return $this->entityManager->createQuery('
            SELECT a.id, a.views, a.publishedAt, t.title
            FROM App\BoundedContext\Article\Domain\Entity\Article a
            LEFT JOIN a.translations t WITH t.locale = :locale
            WHERE a.status = :status
            ORDER BY a.views DESC
        ')
            ->setParameter('locale', 'en')
            ->setParameter('status', 'PUBLISHED')
            ->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @return array<mixed>
     */
    public function getTrendingArticles(int $days = 7, int $limit = 10): array
    {
        $dateLimit = new \DateTime("-{$days} days");

        return $this->entityManager->createQuery('
            SELECT a.id, a.views, a.publishedAt, t.title,
                   (a.views / CASE 
                       WHEN date_diff(CURRENT_DATE(), a.publishedAt) = 0 THEN 1 
                       ELSE date_diff(CURRENT_DATE(), a.publishedAt) 
                   END) as daily_avg_views
            FROM App\BoundedContext\Article\Domain\Entity\Article a
            LEFT JOIN a.translations t WITH t.locale = :locale
            WHERE a.status = :status 
            AND a.publishedAt >= :dateLimit
            ORDER BY daily_avg_views DESC
        ')
            ->setParameter('locale', 'en')
            ->setParameter('status', 'PUBLISHED')
            ->setParameter('dateLimit', $dateLimit)
            ->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @return array<mixed>
     */
    public function getGlobalStats(): array
    {
        $totalViews = (int) $this->entityManager->createQuery('
            SELECT SUM(a.views) as total
            FROM App\BoundedContext\Article\Domain\Entity\Article a
            WHERE a.status = :status
        ')
            ->setParameter('status', 'PUBLISHED')
            ->getSingleScalarResult();

        $totalArticles = (int) $this->entityManager->createQuery('
            SELECT COUNT(a.id) as total
            FROM App\BoundedContext\Article\Domain\Entity\Article a
            WHERE a.status = :status
        ')
            ->setParameter('status', 'PUBLISHED')
            ->getSingleScalarResult();

        $avgViews = $totalArticles > 0 ? (float) $totalViews / $totalArticles : 0.0;

        return [
            'total_views' => (int) $totalViews,
            'total_articles' => (int) $totalArticles,
            'average_views_per_article' => round($avgViews, 2),
            'most_viewed_today' => $this->getMostViewedToday()
        ];
    }

    /**
     * @return array<mixed>|null
     */
    private function getMostViewedToday(): ?array
    {
        $result = $this->entityManager->createQuery('
            SELECT a.id, a.views, t.title
            FROM App\BoundedContext\Article\Domain\Entity\Article a
            LEFT JOIN a.translations t WITH t.locale = :locale
            WHERE a.status = :status 
            AND a.publishedAt >= :today
            ORDER BY a.views DESC
        ')
            ->setParameter('locale', 'en')
            ->setParameter('status', 'PUBLISHED')
            ->setParameter('today', new \DateTime('today'))
            ->setMaxResults(1)
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * @return array<mixed>
     */
    public function getAuthorStats(int $authorId): array
    {
        $stats = $this->entityManager->createQuery('
            SELECT 
                COUNT(a.id) as total_articles,
                SUM(a.views) as total_views,
                AVG(a.views) as avg_views,
                MAX(a.views) as max_views
            FROM App\BoundedContext\Article\Domain\Entity\Article a
            WHERE a.author = :authorId
            AND a.status = :status
        ')
            ->setParameter('authorId', $authorId)
            ->setParameter('status', 'PUBLISHED')
            ->getSingleResult();

        return [
            'total_articles' => (int) $stats['total_articles'],
            'total_views' => (int) $stats['total_views'],
            'average_views' => round($stats['avg_views'], 2),
            'best_article_views' => (int) $stats['max_views']
        ];
    }

    /**
     * @return array<mixed>
     */
    public function getUnderutilizedArticles(int $maxViews = 10): array
    {
        return $this->entityManager->createQuery('
            SELECT a.id, a.views, a.publishedAt, t.title
            FROM App\BoundedContext\Article\Domain\Entity\Article a
            LEFT JOIN a.translations t WITH t.locale = :locale
            WHERE a.status = :status
            AND a.views <= :maxViews
            ORDER BY a.publishedAt DESC
        ')
            ->setParameter('locale', 'en')
            ->setParameter('status', 'PUBLISHED')
            ->setParameter('maxViews', $maxViews)
            ->getResult();
    }
}
