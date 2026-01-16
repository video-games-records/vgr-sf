<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\Forum\Domain\Entity\Topic;
use App\BoundedContext\Forum\Domain\Entity\TopicType;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Topic::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Topic::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Topic::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Topic::class)]
class TopicListener
{
    /**
     * @var array<string, mixed>
     */
    private array $changeSet = [];
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param Topic $topic
     * @param LifecycleEventArgs $event
     * @phpstan-param LifecycleEventArgs<EntityManagerInterface> $event
     */
    public function prePersist(Topic $topic, LifecycleEventArgs $event): void
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        if ($user instanceof User) {
            $topic->setUser($user);
        }

        // Only set default type if none is specified
        if ($topic->getType() === null) {
            $defaultType = $event->getObjectManager()->getRepository(TopicType::class)->find(3);
            if ($defaultType !== null) {
                $topic->setType($defaultType);
            }
        }

        foreach ($topic->getMessages() as $message) {
            if ($user instanceof User) {
                $message->setUser($user);
            }
        }

        $forum = $topic->getForum();
        $forum->setNbTopic($forum->getNbTopic() + 1);
    }

    /**
     * @param Topic $topic
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Topic $topic, PreUpdateEventArgs $event): void
    {
        $this->changeSet = $event->getEntityChangeSet();
    }

    /**
     * @param Topic              $topic
     * @param LifecycleEventArgs $event
     * @phpstan-param LifecycleEventArgs<EntityManagerInterface> $event
     */
    public function postUpdate(Topic $topic, LifecycleEventArgs $event): void
    {
        if (array_key_exists('forum', $this->changeSet)) {
            $nbMessage = $topic->getNbMessage();
            /** @var Forum $forumSource */
            $forumSource = $this->changeSet['forum'][0];
            /** @var Forum $forumDestination */
            $forumDestination = $this->changeSet['forum'][1];

            $forumSource->setNbTopic($forumSource->getNbTopic() - 1);
            $forumSource->setNbMessage($forumSource->getNbMessage() - $nbMessage);

            $forumDestination->setNbTopic($forumDestination->getNbTopic() + 1);
            $forumDestination->setNbMessage($forumDestination->getNbMessage() + $nbMessage);

            $event->getObjectManager()->flush();
        }
    }

    /**
     * @param Topic              $topic
     * @param LifecycleEventArgs $event
     * @phpstan-param LifecycleEventArgs<EntityManagerInterface> $event
     */
    public function preRemove(Topic $topic, LifecycleEventArgs $event): void
    {
        $nbMessage = $topic->getNbMessage();

        $forum = $topic->getForum();
        $forum->setNbTopic($forum->getNbTopic() - 1);
        $forum->setNbMessage($forum->getNbMessage() - $nbMessage);
    }
}
