<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject;

enum GroupOrderBy: string
{
    case NAME = 'NAME';
    case ID = 'ID';
    case CUSTOM = 'CUSTOM';

    /**
     * @return array<string, string>
     */
    public static function getStatusChoices(): array
    {
        return [
            self::NAME->value => self::NAME->value,
            self::ID->value => self::ID->value,
            self::CUSTOM->value => self::CUSTOM->value,
        ];
    }
}
