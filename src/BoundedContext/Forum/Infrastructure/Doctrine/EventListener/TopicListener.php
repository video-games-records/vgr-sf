<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\Forum\Domain\Entity\Topic;
use App\BoundedContext\Forum\Domain\Entity\TopicType;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Topic::class)]
class TopicListener
{
    /**
     * @param Topic $topic
     * @param LifecycleEventArgs $event
     * @phpstan-param LifecycleEventArgs<EntityManagerInterface> $event
     */
    public function prePersist(Topic $topic, LifecycleEventArgs $event): void
    {
        // Only set default type if none is specified
        if ($topic->getType() === null) {
            $defaultType = $event->getObjectManager()->getRepository(TopicType::class)->find(3);
            if ($defaultType !== null) {
                $topic->setType($defaultType);
            }
        }

        $forum = $topic->getForum();
        $forum->setNbTopic($forum->getNbTopic() + 1);
    }
}
