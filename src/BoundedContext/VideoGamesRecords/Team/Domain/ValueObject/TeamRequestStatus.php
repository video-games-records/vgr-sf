<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Domain\ValueObject;

enum TeamRequestStatus: string
{
    case ACTIVE = 'ACTIVE';
    case ACCEPTED = 'ACCEPTED';
    case CANCELED = 'CANCELED';
    case REFUSED = 'REFUSED';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isAccepted(): bool
    {
        return $this === self::ACCEPTED;
    }

    public function isRefused(): bool
    {
        return $this === self::REFUSED;
    }

    public function isCanceled(): bool
    {
        return $this === self::CANCELED;
    }

    /**
     * @return array<string, string>
     */
    public static function getStatusChoices(): array
    {
        return [
            self::ACTIVE->value => self::ACTIVE->value,
            self::ACCEPTED->value => self::ACCEPTED->value,
            self::REFUSED->value => self::REFUSED->value,
            self::CANCELED->value => self::CANCELED->value,
        ];
    }
}
