<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject;

enum ProofRequestStatus: string
{
    case IN_PROGRESS = 'IN PROGRESS';
    case REFUSED = 'REFUSED';
    case ACCEPTED = 'ACCEPTED';

    public function isInProgress(): bool
    {
        return $this === self::IN_PROGRESS;
    }

    /**
     * @return array<string, string>
     */
    public static function getStatusChoices(): array
    {
        return [
            self::IN_PROGRESS->value => self::IN_PROGRESS->value,
            self::REFUSED->value => self::REFUSED->value,
            self::ACCEPTED->value => self::ACCEPTED->value,
        ];
    }
}
