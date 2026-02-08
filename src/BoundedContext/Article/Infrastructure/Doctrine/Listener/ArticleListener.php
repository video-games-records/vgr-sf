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
    public function __construct(
        private SluggerInterface $slugger,
        private CacheInterface $cache,
    ) {
    }

    public function prePersist(Article $article): void
    {
        if ($article->getArticleStatus()->isPublished()) {
            $article->setPublishedAt(new \DateTime());
            $this->cache->delete(TopNewsController::CACHE_KEY);
        }

        $this->updateSlug($article);
    }

    public function preUpdate(Article $article): void
    {
        if ($article->getArticleStatus()->isPublished() && $article->getPublishedAt() === null) {
            $article->setPublishedAt(new \DateTime());
        }

        if ($article->getArticleStatus()->isPublished()) {
            $this->cache->delete(TopNewsController::CACHE_KEY);
        }

        $this->updateSlug($article);
    }

    private function updateSlug(Article $article): void
    {
        $article->setSlug($this->slugger->slug($article->getDefaultTitle())->lower()->toString());
    }
}
