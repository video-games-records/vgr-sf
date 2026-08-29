<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Infrastructure\Doctrine\Listener;

use App\BoundedContext\Article\Domain\Entity\Article;
use App\BoundedContext\Article\Presentation\Web\Controller\TopNewsController;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Article::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Article::class)]
readonly class ArticleListener
{
    private const array LOCALES = ['en', 'fr', 'de', 'it', 'ja', 'es', 'pt_BR', 'zh_CN'];

    public function __construct(
        private SluggerInterface $slugger,
        private CacheInterface $cache,
    ) {
    }

    public function prePersist(Article $article): void
    {
        if ($article->getArticleStatus()->isPublished()) {
            if ($article->getPublishedAt() === null) {
                $article->setPublishedAt(new \DateTime());
            }
            $this->invalidateNewsCache();
        }

        $this->updateSlug($article);
    }

    public function preUpdate(Article $article): void
    {
        if ($article->getArticleStatus()->isPublished() && $article->getPublishedAt() === null) {
            $article->setPublishedAt(new \DateTime());
        }

        if ($article->getArticleStatus()->isPublished()) {
            $this->invalidateNewsCache();
        }

        $this->updateSlug($article);
    }

    private function invalidateNewsCache(): void
    {
        foreach (self::LOCALES as $locale) {
            $this->cache->delete(TopNewsController::CACHE_KEY . '_' . $locale);
            $this->cache->delete(TopNewsController::CACHE_KEY . '_dashboard_' . $locale);
        }
    }

    private function updateSlug(Article $article): void
    {
        $article->setSlug($this->slugger->slug($article->getDefaultTitle())->lower()->toString());
    }
}
