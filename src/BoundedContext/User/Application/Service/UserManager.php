<?php

namespace App\BoundedContext\User\Application\Service;

use App\SharedKernel\Domain\Interface\EventDispatcherInterface;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Domain\Event\EmailChangedEvent;
use App\BoundedContext\User\Domain\Event\PasswordChangedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class UserManager
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function deleteUser(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }

    public function updateUser(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function changeUserEmail(User $user, string $newEmail): void
    {
        $oldEmail = $user->getEmail();
        $user->setEmail($newEmail);

        $this->em->persist($user);
        $this->em->flush();

        // Dispatch domain event
        $this->eventDispatcher->dispatch(
            new EmailChangedEvent($user, $oldEmail, $newEmail)
        );
    }

    public function changeUserPassword(User $user, string $newPassword): void
    {
        $user->setPassword($newPassword);

        $this->em->persist($user);
        $this->em->flush();

        // Dispatch domain event
        $this->eventDispatcher->dispatch(
            new PasswordChangedEvent($user)
        );
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->findUserBy(['email' => $email]);
    }

    public function findUserByUsername(string $username): ?User
    {
        return $this->findUserBy(['username' => $username]);
    }

    public function findUserByUsernameOrEmail(string $usernameOrEmail): ?User
    {
        if (preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)) {
            $user = $this->findUserByEmail($usernameOrEmail);
            if (null !== $user) {
                return $user;
            }
        }
        return $this->findUserByUsername($usernameOrEmail);
    }

    public function findUserByConfirmationToken(string $token): ?User
    {
        return $this->findUserBy(['confirmationToken' => $token]);
    }

    /**
     * @param array<string, mixed> $criteria
     */
    private function findUserBy(array $criteria): ?User
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * @return ObjectRepository<User>
     */
    protected function getRepository(): ObjectRepository
    {
        return $this->em->getRepository(User::class);
    }
}
