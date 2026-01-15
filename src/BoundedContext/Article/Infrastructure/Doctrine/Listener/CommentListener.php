<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Infrastructure\Doctrine\Listener;

use App\BoundedContext\Article\Domain\Entity\Comment;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use HTMLPurifier;
use HTMLPurifier_Config;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Comment::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Comment::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Comment::class)]
class CommentListener
{
    private HTMLPurifier $purifier;

    public function __construct(
        private readonly Security $security,
    ) {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,strong,em,u,ol,ul,li,a[href],h1,h2,h3,blockquote');
        $this->purifier = new HTMLPurifier($config);
    }

    public function prePersist(Comment $comment): void
    {
        // Only set user from security context if no user is already set and we have an authenticated user
        if (null === $comment->getUser()) {
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $comment->setUser($user);
            }
        }

        $comment->getArticle()->setNbComment($comment->getArticle()->getNbComment() + 1);
        $this->purifyContent($comment);
    }

    public function preUpdate(Comment $comment): void
    {
        $this->purifyContent($comment);
    }

    public function preRemove(Comment $comment): void
    {
        $comment->getArticle()->setNbComment($comment->getArticle()->getNbComment() - 1);
    }

    private function purifyContent(Comment $comment): void
    {
        if ($comment->getContent()) {
            $comment->setContent($this->purifier->purify($comment->getContent()));
        }
    }
}
