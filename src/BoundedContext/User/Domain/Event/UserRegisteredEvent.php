<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Domain\Event;

use App\BoundedContext\User\Domain\Entity\User;
use App\SharedKernel\Domain\Event\DomainEvent;

/**
 * Event dispatched when a new user is registered
 */
class UserRegisteredEvent extends DomainEvent
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
