<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\Forum\Domain\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Topic::class)]
class TopicListener
{
    /**
     * @param Topic $topic
     */
    public function prePersist(Topic $topic): void
    {
        $forum = $topic->getForum();
        $forum->setNbTopic($forum->getNbTopic() + 1);
    }
}
