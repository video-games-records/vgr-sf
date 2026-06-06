<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Infrastructure\Event\Listener;

use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsEventListener(event: KernelEvents::CONTROLLER)]
class UserActivityListener
{
    private const int UPDATE_INTERVAL_SECONDS = 900; // 15 minutes

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function __invoke(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return;
        }

        $lastLogin = $user->getLastLogin();
        $now = new \DateTime();

        if ($lastLogin !== null) {
            $diffSeconds = $now->getTimestamp() - $lastLogin->getTimestamp();
            if ($diffSeconds < self::UPDATE_INTERVAL_SECONDS) {
                return;
            }
        }

        $user->setLastLogin($now);
        $this->em->flush();
    }
}
