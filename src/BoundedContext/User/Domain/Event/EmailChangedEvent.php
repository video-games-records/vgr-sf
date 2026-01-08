<?php

namespace App\BoundedContext\User\Domain\Event;

use App\BoundedContext\User\Domain\Entity\User;
use App\SharedKernel\Domain\Event\DomainEvent;

class EmailChangedEvent extends DomainEvent
{
    public function __construct(
        private readonly User $user,
        private readonly string $oldEmail,
        private readonly string $newEmail
    ) {
        parent::__construct();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getOldEmail(): string
    {
        return $this->oldEmail;
    }

    public function getNewEmail(): string
    {
        return $this->newEmail;
    }
}
