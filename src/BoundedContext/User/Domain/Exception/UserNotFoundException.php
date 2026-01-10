<?php

namespace App\BoundedContext\User\Domain\Exception;

use App\SharedKernel\Domain\Exception\EntityNotFoundException;

class UserNotFoundException extends EntityNotFoundException
{
    public function __construct(int|string $identifier)
    {
        parent::__construct('User', $identifier);
    }
}
