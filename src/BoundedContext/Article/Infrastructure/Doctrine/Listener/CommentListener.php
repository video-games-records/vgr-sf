<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Infrastructure\Doctrine\Listener;

use App\BoundedContext\Article\Domain\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Comment::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Comment::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Comment::class)]
class CommentListener
{
    public function __construct(
        #[Autowire(service: 'html_sanitizer.sanitizer.app.content_sanitizer')]
        private readonly HtmlSanitizerInterface $sanitizer,
    ) {
    }

    public function prePersist(Comment $comment): void
    {
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
            $comment->setContent($this->sanitizer->sanitize($comment->getContent()));
        }
    }
}
