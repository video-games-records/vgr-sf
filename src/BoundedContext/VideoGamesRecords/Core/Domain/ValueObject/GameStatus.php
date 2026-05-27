<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject;

enum GameStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
    case CREATED = 'CREATED';
    case ADD_PICTURE = 'ADD_PICTURE';
    case ADD_SCORE = 'ADD_SCORE';
    case COMPLETED = 'COMPLETED';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this === self::INACTIVE;
    }

    /**
     * @return array<string, string>
     */
    public static function getStatusChoices(): array
    {
        return [
            self::CREATED->value . ' (1)' => self::CREATED->value,
            self::ADD_SCORE->value . ' (2)' => self::ADD_SCORE->value,
            self::ADD_PICTURE->value . ' (3)' => self::ADD_PICTURE->value,
            self::COMPLETED->value . ' (4)' => self::COMPLETED->value,
            self::ACTIVE->value . ' (5)' => self::ACTIVE->value,
            self::INACTIVE->value => self::INACTIVE->value,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getReverseStatusChoices(): array
    {
        return [
            self::CREATED->value => self::CREATED->value . ' (1)',
            self::ADD_SCORE->value => self::ADD_SCORE->value . ' (2)',
            self::ADD_PICTURE->value => self::ADD_PICTURE->value . ' (3)',
            self::COMPLETED->value => self::COMPLETED->value . ' (4)',
            self::ACTIVE->value => self::ACTIVE->value . ' (5)',
            self::INACTIVE->value => self::INACTIVE->value,
        ];
    }
}
