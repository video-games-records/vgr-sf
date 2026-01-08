<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Infrastructure\Event\Listener;

use App\BoundedContext\User\Application\Service\SecurityHistoryManager;
use App\BoundedContext\User\Application\Service\UserManager;
use App\SharedKernel\Domain\Security\SecurityEventTypeEnum;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Event\RefreshEvent;

/**
 * Listener pour les rafraîchissements de tokens JWT
 */
class RefreshTokenListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserManager $userManager,
        private readonly SecurityHistoryManager $securityHistoryManager,
    ) {
    }

    /**
     * Appelé lors du rafraîchissement d'un token JWT
     */
    public function onRefreshToken(RefreshEvent $event): void
    {
        // Récupérer les données du refresh token
        $refreshToken = $event->getRefreshToken();
        $username = $refreshToken->getUsername();

        // Trouver l'utilisateur
        $user = $this->userManager->findUserByUsernameOrEmail($username);

        if (!$user instanceof User) {
            return;
        }

        // Mettre à jour le lastLogin
        $user->setLastLogin(new \DateTime());

        // Sauvegarder
        $this->entityManager->flush();

        // Enregistrer l'événement de sécurité
        $this->securityHistoryManager->recordEvent(
            $user,
            SecurityEventTypeEnum::TOKEN_REFRESH,
            [
                'refresh_token' => true,
                'token_id' => $refreshToken->getId()
            ]
        );
    }
}
