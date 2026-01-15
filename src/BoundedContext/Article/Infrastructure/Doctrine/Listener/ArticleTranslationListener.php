<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Infrastructure\Doctrine\Listener;

use App\BoundedContext\Article\Domain\Entity\ArticleTranslation;
use Datetime;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: ArticleTranslation::class)]
class ArticleTranslationListener
{
    public function postUpdate(ArticleTranslation $translation, PostUpdateEventArgs $event): void
    {
        $article = $translation->getTranslatable();

        $em = $event->getObjectManager();

        $article->setUpdatedAt(new DateTime());

        $em->persist($article);
    }
}
