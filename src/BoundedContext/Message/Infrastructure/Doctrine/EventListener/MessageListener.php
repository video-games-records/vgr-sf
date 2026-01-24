<?php

declare(strict_types=1);

namespace App\BoundedContext\Message\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\Message\Domain\Entity\Message;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Message::class)]
readonly class MessageListener
{
    public function __construct(private Security $security)
    {
    }

    public function prePersist(Message $message): void
    {
        if (null === $message->getSender()) {
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $message->setSender($user);
            }
        }
    }
}
