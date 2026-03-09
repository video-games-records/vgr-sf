<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\Forum\Domain\Entity\Message;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Message::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Message::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Message::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: Message::class)]
class MessageListener
{
    public function __construct(
        #[Autowire(service: 'html_sanitizer.sanitizer.app.content_sanitizer')]
        private readonly HtmlSanitizerInterface $sanitizer,
    ) {
    }

    public function preUpdate(Message $message): void
    {
        $this->purifyMessage($message);
    }

    private function purifyMessage(Message $message): void
    {
        if ($message->getMessage()) {
            $message->setMessage($this->sanitizer->sanitize($message->getMessage()));
        }
    }

    /**
     * @param Message $message
     * @param LifecycleEventArgs $event
     * @phpstan-param LifecycleEventArgs<EntityManagerInterface> $event
     */
    public function prePersist(Message $message, LifecycleEventArgs $event): void
    {
        $this->purifyMessage($message);

        // Update user message count
        $user = $message->getUser();
        $user->setNbForumMessage($user->getNbForumMessage() + 1);

        $topic = $message->getTopic();
        $topic->setNbMessage($topic->getNbMessage() + 1);
        $topic->setLastMessage($message);
        $topic->setBoolArchive(false);
        $message->setPosition($topic->getNbMessage() + 1);

        $forum = $topic->getForum();
        $forum->setNbMessage($forum->getNbMessage() + 1);
        $forum->setLastMessage($message);
    }

    /**
     * @param Message $message
     * @param LifecycleEventArgs $event
     * @phpstan-param LifecycleEventArgs<EntityManagerInterface> $event
     */
    public function preRemove(Message $message, LifecycleEventArgs $event): void
    {
        $topic = $message->getTopic();
        $topic->setNbMessage($topic->getNbMessage() - 1);

        $i = 1;
        foreach ($topic->getMessages() as $row) {
            $row->setPosition($i);
            $i++;
        }

        $forum = $topic->getForum();
        $forum->setNbMessage($forum->getNbMessage() - 1);
    }

    /**
     * @param Message $message
     * @param LifecycleEventArgs $event
     * @phpstan-param LifecycleEventArgs<EntityManagerInterface> $event
     * @return void
     */
    public function postRemove(Message $message, LifecycleEventArgs $event): void
    {
        $topic = $message->getTopic();
        $forum = $topic->getForum();
        /** @var Message $lastMessage */
        $lastMessage = $topic->getMessages()->last();
        if ($message === $topic->getLastMessage()) {
            $topic->setLastMessage($lastMessage);
            $event->getObjectManager()->flush();
        }
        if ($message === $forum->getLastMessage()) {
            $forum->setLastMessage($lastMessage);
            $event->getObjectManager()->flush();
        }
    }
}
