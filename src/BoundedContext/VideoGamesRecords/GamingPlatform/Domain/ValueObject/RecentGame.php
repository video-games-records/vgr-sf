<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject;

readonly class RecentGame
{
    public function __construct(
        public int $appId,
        public string $name,
        public ?int $playtimeForeverMinutes,
        public ?int $playtime2WeeksMinutes,
        public string $iconUrl,
    ) {
    }

    public function hasPlaytimeData(): bool
    {
        return $this->playtimeForeverMinutes !== null;
    }

    public function getPlaytimeForeverHours(): ?int
    {
        return $this->playtimeForeverMinutes !== null ? intdiv($this->playtimeForeverMinutes, 60) : null;
    }

    public function getPlaytime2WeeksHours(): ?float
    {
        return $this->playtime2WeeksMinutes !== null ? round($this->playtime2WeeksMinutes / 60, 1) : null;
    }
}
