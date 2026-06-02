<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\GamingPlatform\Unit\Domain\ValueObject;

use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\RecentGame;
use PHPUnit\Framework\TestCase;

class RecentGameTest extends TestCase
{
    private function makeGame(int $playtimeForever, int $playtime2Weeks): RecentGame
    {
        return new RecentGame(
            appId: 12345,
            name: 'Test Game',
            playtimeForeverMinutes: $playtimeForever,
            playtime2WeeksMinutes: $playtime2Weeks,
            iconUrl: 'https://example.com/icon.jpg',
        );
    }

    public function testGetPlaytimeForeverHoursReturnsIntDiv(): void
    {
        $game = $this->makeGame(125, 0);
        $this->assertSame(2, $game->getPlaytimeForeverHours());
    }

    public function testGetPlaytimeForeverHoursWithZeroMinutes(): void
    {
        $game = $this->makeGame(0, 0);
        $this->assertSame(0, $game->getPlaytimeForeverHours());
    }

    public function testGetPlaytimeForeverHoursExactHour(): void
    {
        $game = $this->makeGame(60, 0);
        $this->assertSame(1, $game->getPlaytimeForeverHours());
    }

    public function testGetPlaytime2WeeksHoursReturnsRounded(): void
    {
        $game = $this->makeGame(0, 90);
        $this->assertSame(1.5, $game->getPlaytime2WeeksHours());
    }

    public function testGetPlaytime2WeeksHoursWithZeroMinutes(): void
    {
        $game = $this->makeGame(0, 0);
        $this->assertSame(0.0, $game->getPlaytime2WeeksHours());
    }

    public function testGetPlaytime2WeeksHoursRoundsToOneDecimal(): void
    {
        // 100 minutes = 1.666... → rounded to 1.7
        $game = $this->makeGame(0, 100);
        $this->assertSame(1.7, $game->getPlaytime2WeeksHours());
    }

    public function testConstructorStoresProperties(): void
    {
        $game = new RecentGame(
            appId: 99,
            name: 'My Game',
            playtimeForeverMinutes: 300,
            playtime2WeeksMinutes: 60,
            iconUrl: 'https://example.com/icon.png',
        );

        $this->assertSame(99, $game->appId);
        $this->assertSame('My Game', $game->name);
        $this->assertSame(300, $game->playtimeForeverMinutes);
        $this->assertSame(60, $game->playtime2WeeksMinutes);
        $this->assertSame('https://example.com/icon.png', $game->iconUrl);
    }
}
