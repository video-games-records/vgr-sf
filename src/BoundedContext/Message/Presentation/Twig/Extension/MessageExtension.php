<?php

declare(strict_types=1);

namespace App\BoundedContext\Message\Presentation\Twig\Extension;

use App\BoundedContext\Message\Domain\Repository\MessageRepositoryInterface;
use App\BoundedContext\User\Domain\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MessageExtension extends AbstractExtension
{
    public function __construct(
        private readonly MessageRepositoryInterface $messageRepository,
        private readonly Security $security,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_unread_messages_count', [$this, 'getUnreadMessagesCount']),
        ];
    }

    public function getUnreadMessagesCount(): int
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return 0;
        }

        return $this->messageRepository->getNbNewMessage($user);
    }
}
