<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\LostPosition;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use PHPUnit\Framework\TestCase;

class LostPositionTest extends TestCase
{
    private LostPosition $lostPosition;

    protected function setUp(): void
    {
        $this->lostPosition = new LostPosition();
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->lostPosition->getId());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->lostPosition->setId(7);
        $this->assertSame(7, $this->lostPosition->getId());
        $this->assertSame($this->lostPosition, $result);
    }

    public function testOldRankDefaultsToZero(): void
    {
        $this->assertSame(0, $this->lostPosition->getOldRank());
    }

    public function testSetAndGetOldRank(): void
    {
        $result = $this->lostPosition->setOldRank(3);
        $this->assertSame(3, $this->lostPosition->getOldRank());
        $this->assertSame($this->lostPosition, $result);
    }

    public function testNewRankDefaultsToZero(): void
    {
        $this->assertSame(0, $this->lostPosition->getNewRank());
    }

    public function testSetAndGetNewRank(): void
    {
        $result = $this->lostPosition->setNewRank(5);
        $this->assertSame(5, $this->lostPosition->getNewRank());
        $this->assertSame($this->lostPosition, $result);
    }

    // ------------------------------------------------------------------
    // Player relation
    // ------------------------------------------------------------------

    public function testSetAndGetPlayer(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->lostPosition->setPlayer($player);
        $this->assertSame($player, $this->lostPosition->getPlayer());
        $this->assertSame($this->lostPosition, $result);
    }

    // ------------------------------------------------------------------
    // Chart relation
    // ------------------------------------------------------------------

    public function testSetAndGetChart(): void
    {
        $chart = $this->createMock(Chart::class);
        $result = $this->lostPosition->setChart($chart);
        $this->assertSame($chart, $this->lostPosition->getChart());
        $this->assertSame($this->lostPosition, $result);
    }
}
