<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Exception;

class EntityNotFoundException extends DomainException
{
    public function __construct(string $entityType, int|string $identifier)
    {
        parent::__construct(
            sprintf('%s with identifier "%s" not found', $entityType, $identifier)
        );
    }
}
