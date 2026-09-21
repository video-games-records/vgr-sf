<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Exception;

class PictureAlreadyExistsException extends DomainException
{
    public function __construct(private readonly string $filename)
    {
        parent::__construct(
            sprintf('A picture named "%s" already exists.', $filename)
        );
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
