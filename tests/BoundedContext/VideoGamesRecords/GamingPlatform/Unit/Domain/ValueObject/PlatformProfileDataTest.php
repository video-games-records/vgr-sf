<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\GamingPlatform\Unit\Domain\ValueObject;

use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformProfileData;
use PHPUnit\Framework\TestCase;

class PlatformProfileDataTest extends TestCase
{
    public function testIsOnlineReturnsTrueWhenStatusIsOne(): void
    {
        $data = new PlatformProfileData(
            externalId: '123',
            username: 'player',
            onlineStatus: 1,
        );
        $this->assertTrue($data->isOnline());
    }

    public function testIsOnlineReturnsFalseWhenStatusIsZero(): void
    {
        $data = new PlatformProfileData(
            externalId: '123',
            username: 'player',
            onlineStatus: 0,
        );
        $this->assertFalse($data->isOnline());
    }

    public function testDefaultOnlineStatusIsFalse(): void
    {
        $data = new PlatformProfileData(externalId: '123', username: 'player');
        $this->assertFalse($data->isOnline());
    }

    public function testConstructorStoresAllProperties(): void
    {
        $data = new PlatformProfileData(
            externalId: 'ext_42',
            username: 'gamer',
            avatarUrl: 'https://example.com/avatar.png',
            gamesCount: 150,
            onlineStatus: 1,
            recentGames: [],
        );

        $this->assertSame('ext_42', $data->externalId);
        $this->assertSame('gamer', $data->username);
        $this->assertSame('https://example.com/avatar.png', $data->avatarUrl);
        $this->assertSame(150, $data->gamesCount);
        $this->assertSame([], $data->recentGames);
    }

    public function testDefaultsAreNullableFields(): void
    {
        $data = new PlatformProfileData(externalId: 'id', username: 'user');
        $this->assertNull($data->avatarUrl);
        $this->assertNull($data->gamesCount);
        $this->assertSame([], $data->recentGames);
    }
}
