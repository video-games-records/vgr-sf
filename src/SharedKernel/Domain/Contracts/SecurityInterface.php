<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Contracts;

interface SecurityInterface
{
    public const string ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const string ROLE_GAME_COPY = 'ROLE_GAME_COPY';
}
