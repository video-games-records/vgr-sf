<?php

namespace App\BoundedContext\User\Domain\Event;

use App\BoundedContext\User\Domain\Entity\User;
use App\SharedKernel\Domain\Event\DomainEvent;

class PasswordChangedEvent extends DomainEvent
{
    public function __construct(
        private readonly User $user
    ) {
        parent::__construct();
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
