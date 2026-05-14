<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject;

readonly class PlatformProfileData
{
    /** @param list<RecentGame> $recentGames */
    public function __construct(
        public string $externalId,
        public string $username,
        public ?string $avatarUrl = null,
        public ?int $gamesCount = null,
        public int $onlineStatus = 0,
        public array $recentGames = [],
    ) {
    }

    public function isOnline(): bool
    {
        return $this->onlineStatus === 1;
    }
}
