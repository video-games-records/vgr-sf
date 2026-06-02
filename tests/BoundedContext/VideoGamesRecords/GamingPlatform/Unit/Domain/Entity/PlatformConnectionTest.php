<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\GamingPlatform\Unit\Domain\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Entity\PlatformConnection;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use PHPUnit\Framework\TestCase;

class PlatformConnectionTest extends TestCase
{
    private Player $player;

    protected function setUp(): void
    {
        $this->player = new Player();
    }

    private function makeConnection(
        string $externalId = 'ext123',
        ?string $username = 'gamer',
    ): PlatformConnection {
        return new PlatformConnection(
            player: $this->player,
            platform: PlatformEnum::STEAM,
            externalId: $externalId,
            username: $username,
        );
    }

    public function testIdDefaultsToNull(): void
    {
        $connection = $this->makeConnection();
        $this->assertNull($connection->getId());
    }

    public function testGetPlayer(): void
    {
        $connection = $this->makeConnection();
        $this->assertSame($this->player, $connection->getPlayer());
    }

    public function testGetPlatform(): void
    {
        $connection = $this->makeConnection();
        $this->assertSame(PlatformEnum::STEAM, $connection->getPlatform());
    }

    public function testGetExternalId(): void
    {
        $connection = $this->makeConnection('steam_id_999');
        $this->assertSame('steam_id_999', $connection->getExternalId());
    }

    public function testGetUsername(): void
    {
        $connection = $this->makeConnection(username: 'PlayerOne');
        $this->assertSame('PlayerOne', $connection->getUsername());
    }

    public function testUsernameCanBeNull(): void
    {
        $connection = $this->makeConnection(username: null);
        $this->assertNull($connection->getUsername());
    }

    public function testLinkedAtIsSetOnConstruction(): void
    {
        $before = new \DateTimeImmutable();
        $connection = $this->makeConnection();
        $after = new \DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $connection->getLinkedAt());
        $this->assertLessThanOrEqual($after, $connection->getLinkedAt());
    }

    public function testUpdateUsername(): void
    {
        $connection = $this->makeConnection(username: 'OldName');
        $connection->updateUsername('NewName');
        $this->assertSame('NewName', $connection->getUsername());
    }

    public function testUpdateUsernameToNull(): void
    {
        $connection = $this->makeConnection(username: 'SomeName');
        $connection->updateUsername(null);
        $this->assertNull($connection->getUsername());
    }

    public function testTokenDataDefaultsToNull(): void
    {
        $connection = $this->makeConnection();
        $this->assertNull($connection->getTokenData());
    }

    public function testSetTokenData(): void
    {
        $connection = $this->makeConnection();
        $connection->setTokenData('{"access":"token123"}');
        $this->assertSame('{"access":"token123"}', $connection->getTokenData());
    }

    public function testSetTokenDataToNull(): void
    {
        $connection = $this->makeConnection();
        $connection->setTokenData('some_token');
        $connection->setTokenData(null);
        $this->assertNull($connection->getTokenData());
    }
}
