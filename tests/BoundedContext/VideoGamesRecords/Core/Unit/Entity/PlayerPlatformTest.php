<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerPlatform;
use PHPUnit\Framework\TestCase;

class PlayerPlatformTest extends TestCase
{
    private PlayerPlatform $playerPlatform;

    protected function setUp(): void
    {
        $this->playerPlatform = new PlayerPlatform();
    }

    // ------------------------------------------------------------------
    // Basic properties (trait defaults)
    // ------------------------------------------------------------------

    public function testNbChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerPlatform->getNbChart());
    }

    public function testSetAndGetNbChart(): void
    {
        $this->playerPlatform->setNbChart(15);
        $this->assertSame(15, $this->playerPlatform->getNbChart());
    }

    // ------------------------------------------------------------------
    // Own properties
    // ------------------------------------------------------------------

    public function testPointPlatformDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerPlatform->getPointPlatform());
    }

    public function testSetAndGetPointPlatform(): void
    {
        $result = $this->playerPlatform->setPointPlatform(200);
        $this->assertSame(200, $this->playerPlatform->getPointPlatform());
        $this->assertSame($this->playerPlatform, $result);
    }

    public function testSetAndGetRankPointPlatform(): void
    {
        $result = $this->playerPlatform->setRankPointPlatform(5);
        $this->assertSame(5, $this->playerPlatform->getRankPointPlatform());
        $this->assertSame($this->playerPlatform, $result);
    }

    // ------------------------------------------------------------------
    // Platform relation
    // ------------------------------------------------------------------

    public function testSetAndGetPlatform(): void
    {
        $platform = $this->createMock(Platform::class);
        $result = $this->playerPlatform->setPlatform($platform);
        $this->assertSame($platform, $this->playerPlatform->getPlatform());
        $this->assertSame($this->playerPlatform, $result);
    }

    // ------------------------------------------------------------------
    // Player relation
    // ------------------------------------------------------------------

    public function testSetAndGetPlayer(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->playerPlatform->setPlayer($player);
        $this->assertSame($player, $this->playerPlatform->getPlayer());
        $this->assertSame($this->playerPlatform, $result);
    }
}
