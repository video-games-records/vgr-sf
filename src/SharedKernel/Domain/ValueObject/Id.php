<?php

namespace App\SharedKernel\Domain\ValueObject;

use InvalidArgumentException;

abstract class Id
{
    protected readonly int $value;

    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('ID must be a positive integer');
        }

        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(Id $other): bool
    {
        return $this->value === $other->getValue() && get_class($this) === get_class($other);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
