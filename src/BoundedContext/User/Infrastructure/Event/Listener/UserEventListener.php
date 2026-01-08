<?php

namespace App\BoundedContext\User\Infrastructure\Event\Listener;

use App\BoundedContext\User\Application\Service\SecurityHistoryManager;
use App\SharedKernel\Domain\Security\SecurityEventTypeEnum;
use App\BoundedContext\User\Domain\Event\EmailChangedEvent;
use App\BoundedContext\User\Domain\Event\PasswordChangedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class UserEventListener
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly SecurityHistoryManager $securityHistoryManager
    ) {
    }

    #[AsEventListener(EmailChangedEvent::class)]
    public function onEmailChanged(EmailChangedEvent $event): void
    {
        $user = $event->getUser();

        $this->logger->info('User email changed', [
            'user_id' => $user->getId(),
            'username' => $user->getUsername(),
            'old_email' => $event->getOldEmail(),
            'new_email' => $event->getNewEmail(),
        ]);

        // Record security event
        $this->securityHistoryManager->recordEvent(
            $user,
            SecurityEventTypeEnum::EMAIL_CHANGE,
            [
                'old_email' => $event->getOldEmail(),
                'new_email' => $event->getNewEmail()
            ]
        );

        // Here we could add other actions:
        // - Send confirmation email
        // - Invalidate existing tokens
        // - etc.
    }

    #[AsEventListener(PasswordChangedEvent::class)]
    public function onPasswordChanged(PasswordChangedEvent $event): void
    {
        $user = $event->getUser();

        $this->logger->info('User password changed', [
            'user_id' => $user->getId(),
            'username' => $user->getUsername(),
        ]);

        // Record security event
        $this->securityHistoryManager->recordEvent(
            $user,
            SecurityEventTypeEnum::PASSWORD_CHANGE
        );

        // Here we could add other actions:
        // - Invalidate all refresh tokens
        // - Send notification email
        // - etc.
    }
}
