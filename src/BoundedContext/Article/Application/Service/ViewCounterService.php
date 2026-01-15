<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Application\Service;

use App\BoundedContext\Article\Domain\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ViewCounterService
{
    private const int CACHE_DURATION = 3600;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
        private readonly CacheItemPoolInterface $cache
    ) {
    }

    public function incrementView(Article $article): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $userIp = $request->getClientIp();
        if (!$userIp) {
            return;
        }

        $cacheKey = sprintf('article_view_%d_%s', $article->getId(), md5($userIp));

        $hasViewed = $this->cache->hasItem($cacheKey);

        if (!$hasViewed) {
            $cacheItem = $this->cache->getItem($cacheKey);
            $cacheItem->set(true);
            $cacheItem->expiresAfter(self::CACHE_DURATION);
            $this->cache->save($cacheItem);

            $this->entityManager->getConnection()->executeStatement(
                'UPDATE pna_article SET views = views + 1 WHERE id = :id',
                ['id' => $article->getId()]
            );
        }
    }
}
