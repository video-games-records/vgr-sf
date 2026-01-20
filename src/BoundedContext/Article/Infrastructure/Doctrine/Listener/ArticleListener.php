<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Infrastructure\Doctrine\Listener;

use App\BoundedContext\Article\Domain\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Article::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Article::class)]
readonly class ArticleListener
{
    public function __construct(
        private SluggerInterface $slugger
    ) {
    }

    public function prePersist(Article $article): void
    {
        if ($article->getArticleStatus()->isPublished()) {
            $article->setPublishedAt(new \DateTime());
        }

        $this->updateSlug($article);
    }

    public function preUpdate(Article $article): void
    {
        if ($article->getArticleStatus()->isPublished() && $article->getPublishedAt() === null) {
            $article->setPublishedAt(new \DateTime());
        }

        $this->updateSlug($article);
    }

    private function updateSlug(Article $article): void
    {
        $article->setSlug($this->slugger->slug($article->getDefaultTitle())->lower()->toString());
    }
}
