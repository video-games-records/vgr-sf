<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject;

enum PlatformEnum: string
{
    case STEAM = 'steam';
    case XBOX = 'xbox';
    case PSN = 'psn';
    case EPIC = 'epic';
    case GOG = 'gog';
    case BATTLENET = 'battlenet';
    case NINTENDO = 'nintendo';
    case RETRO_ACHIEVEMENTS = 'retro_achievements';

    public function getLabel(): string
    {
        return match ($this) {
            self::STEAM => 'Steam',
            self::XBOX => 'Xbox Live',
            self::PSN => 'PlayStation Network',
            self::EPIC => 'Epic Games',
            self::GOG => 'GOG',
            self::BATTLENET => 'Battle.net',
            self::NINTENDO => 'Nintendo',
            self::RETRO_ACHIEVEMENTS => 'RetroAchievements',
        };
    }

    public function isSupported(): bool
    {
        return match ($this) {
            self::STEAM, self::XBOX, self::RETRO_ACHIEVEMENTS => true,
            default => false,
        };
    }

    public function profileUrl(string $externalId): string
    {
        return match ($this) {
            self::STEAM => 'https://steamcommunity.com/profiles/' . $externalId,
            self::XBOX => 'https://www.xbox.com/play/user/' . $externalId,
            self::PSN => 'https://psnprofiles.com/' . $externalId,
            self::EPIC => 'https://www.epicgames.com/id/' . $externalId,
            self::GOG => 'https://www.gog.com/u/' . $externalId,
            self::BATTLENET => 'https://battle.net',
            self::NINTENDO => 'https://www.nintendo.com',
            self::RETRO_ACHIEVEMENTS => 'https://retroachievements.org/user/' . $externalId,
        };
    }

    /** @return list<self> */
    public static function supported(): array
    {
        return array_values(array_filter(self::cases(), fn(self $p) => $p->isSupported()));
    }
}
