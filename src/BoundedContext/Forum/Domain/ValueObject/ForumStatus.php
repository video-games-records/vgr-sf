<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Domain\ValueObject;

enum ForumStatus: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';

    public function isPrivate(): bool
    {
        return $this === self::PRIVATE;
    }

    public function isPublic(): bool
    {
        return $this === self::PUBLIC;
    }

    /**
     * @return string[]
     */
    public static function getStatusChoices(): array
    {
        return [
            self::PUBLIC->value => self::PUBLIC->value,
            self::PRIVATE->value => self::PRIVATE->value,
        ];
    }
}
