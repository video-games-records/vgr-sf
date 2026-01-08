<?php

namespace App\BoundedContext\User\Infrastructure\Event\Listener;

use App\BoundedContext\User\Application\Service\SecurityHistoryManager;
use App\SharedKernel\Domain\Security\SecurityEventTypeEnum;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Events as LexikEvents;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoginListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly SecurityHistoryManager $securityHistoryManager,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LexikEvents::AUTHENTICATION_SUCCESS => 'success'
        ];
    }

    public function success(AuthenticationSuccessEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();

        // Mettre à jour lastLogin avec la date/heure actuelle
        $user->setLastLogin(new \DateTime());

        $this->em->flush();

        $this->securityHistoryManager->recordEvent($user, SecurityEventTypeEnum::LOGIN_SUCCESS);
    }
}
