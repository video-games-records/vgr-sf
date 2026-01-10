<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Application\Service;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Domain\Event\UserRegisteredEvent;
use App\SharedKernel\Domain\Interface\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Service responsible for user registration business logic
 */
readonly class UserRegistrationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * Register a new user
     *
     * @param User $user The user entity with plainPassword set
     * @param bool $autoEnable Whether to enable the user immediately (true) or require email confirmation (false)
     * @return User The registered user
     */
    public function registerUser(User $user, bool $autoEnable = true): User
    {
        // Hash the plain password
        $plainPassword = $user->getPlainPassword();
        if ($plainPassword === null) {
            throw new \InvalidArgumentException('Plain password must be set before registration');
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Erase plain password for security
        $user->setPlainPassword(null);

        // Set default values
        $user->setEnabled($autoEnable);

        // Generate confirmation token if email confirmation is required
        if (!$autoEnable) {
            $confirmationToken = $this->generateConfirmationToken();
            $user->setConfirmationToken($confirmationToken);
        }

        // Persist the user
        $this->em->persist($user);
        $this->em->flush();

        // Dispatch domain event
        $this->eventDispatcher->dispatch(
            new UserRegisteredEvent($user)
        );

        return $user;
    }

    /**
     * Generate a unique confirmation token
     */
    private function generateConfirmationToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Confirm user registration with token
     */
    public function confirmRegistration(User $user): void
    {
        $user->setEnabled(true);
        $user->setConfirmationToken(null);

        $this->em->persist($user);
        $this->em->flush();
    }
}
