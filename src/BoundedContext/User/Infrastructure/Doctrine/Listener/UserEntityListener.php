<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Infrastructure\Doctrine\Listener;

use App\BoundedContext\User\Application\Service\SecurityHistoryManager;
use App\SharedKernel\Domain\Security\SecurityEventTypeEnum;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Domain\Event\EmailChangedEvent;
use App\BoundedContext\User\Domain\Event\PasswordChangedEvent;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: User::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: User::class)]
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: User::class)]
class UserEntityListener
{
    /** @var array<int, array{oldEmail: string, newEmail: string}> */
    private array $emailChangeData = [];
    /** @var array<int, bool> */
    private array $passwordChangeData = [];

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SecurityHistoryManager $securityHistoryManager
    ) {
    }

    /**
     * @throws Exception
     */
    public function prePersist(User $user, PrePersistEventArgs $event): void
    {
        $this->generatePassword($user);
    }

    public function preUpdate(User $user, PreUpdateEventArgs $event): void
    {
        $originalPassword = $user->getPassword();
        $this->generatePassword($user);

        // Check if password was actually changed
        if ($originalPassword !== $user->getPassword() && $user->getPlainPassword() !== null) {
            $this->passwordChangeData[$user->getId()] = true;
        }

        // Check if email changed
        if ($event->hasChangedField('email')) {
            $oldEmail = $event->getOldValue('email');
            $newEmail = $event->getNewValue('email');

            $this->emailChangeData[$user->getId()] = [
                'oldEmail' => $oldEmail,
                'newEmail' => $newEmail
            ];
        }
    }

    public function postUpdate(User $user, PostUpdateEventArgs $event): void
    {
        // Dispatch email changed event
        if (isset($this->emailChangeData[$user->getId()])) {
            $data = $this->emailChangeData[$user->getId()];

            $emailChangedEvent = new EmailChangedEvent($user, $data['oldEmail'], $data['newEmail']);
            $this->eventDispatcher->dispatch($emailChangedEvent);

            unset($this->emailChangeData[$user->getId()]);
        }

        // Dispatch password changed event
        if (isset($this->passwordChangeData[$user->getId()])) {
            $passwordChangedEvent = new PasswordChangedEvent($user);
            $this->eventDispatcher->dispatch($passwordChangedEvent);

            unset($this->passwordChangeData[$user->getId()]);
        }
    }

    public function postPersist(User $user, PostPersistEventArgs $event): void
    {
        $this->securityHistoryManager->recordEvent($user, SecurityEventTypeEnum::REGISTRATION, [
            'email' => $user->getEmail(),
            'username' => $user->getUsername()
        ]);
    }

    private function generatePassword(User $user): void
    {
        $plaintextPassword = $user->getPlainPassword();

        if ($plaintextPassword === null) {
            return;
        }

        // hash the password (based on the security.yaml config for the $user class)
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);

        // Clear plain password for security
        $user->eraseCredentials();
    }
}
