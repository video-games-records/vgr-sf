<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Video\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\VideoComment;
use Doctrine\ORM\Exception\ORMException;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: VideoComment::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: VideoComment::class)]
class VideoCommentListener
{
    /**
     * @param VideoComment $comment
     */
    public function prePersist(VideoComment $comment): void
    {
        $comment->getVideo()->setNbComment($comment->getVideo()->getNbComment() + 1);
    }


    /**
     * @param VideoComment $comment
     */
    public function preRemove(VideoComment $comment): void
    {
        $comment->getVideo()->setNbComment($comment->getVideo()->getNbComment() - 1);
    }
}
