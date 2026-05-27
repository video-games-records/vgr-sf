<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Video\Domain\ValueObject;

enum VideoType: string
{
    case YOUTUBE = 'Youtube';
    case TWITCH = 'Twitch';
    case UNKNOWN = 'Unknown';

    /**
     * @return array<string, string>
     */
    public static function getTypeChoices(): array
    {
        return [
            self::YOUTUBE->value => self::YOUTUBE->value,
            self::TWITCH->value => self::TWITCH->value,
            self::UNKNOWN->value => self::UNKNOWN->value,
        ];
    }
}
