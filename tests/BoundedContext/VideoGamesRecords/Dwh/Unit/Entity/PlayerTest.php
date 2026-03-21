<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Dwh\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Player;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
    private Player $player;

    protected function setUp(): void
    {
        $this->player = new Player();
        $this->player->setId(1);
        $this->player->setDate('2025-01-01');
    }

    // ------------------------------------------------------------------
    // id
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $this->player->setId(42);
        $this->assertSame(42, $this->player->getId());
    }

    public function testSetIdReturnsSelf(): void
    {
        $result = $this->player->setId(10);
        $this->assertSame($this->player, $result);
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->player->setId(7);
        $this->assertSame('7 [7]', (string) $this->player);
    }

    // ------------------------------------------------------------------
    // DateTrait
    // ------------------------------------------------------------------

    public function testSetAndGetDate(): void
    {
        $this->player->setDate('2025-06-15');
        $this->assertSame('2025-06-15', $this->player->getDate());
    }

    public function testSetDateReturnsSelf(): void
    {
        $result = $this->player->setDate('2025-01-01');
        $this->assertSame($this->player, $result);
    }

    // ------------------------------------------------------------------
    // NbPostDayTrait
    // ------------------------------------------------------------------

    public function testNbPostDayDefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getNbPostDay());
    }

    public function testSetAndGetNbPostDay(): void
    {
        $this->player->setNbPostDay(5);
        $this->assertSame(5, $this->player->getNbPostDay());
    }

    public function testSetNbPostDayReturnsSelf(): void
    {
        $result = $this->player->setNbPostDay(3);
        $this->assertSame($this->player, $result);
    }

    // ------------------------------------------------------------------
    // ChartRank0Trait – ChartRank5Trait (from shared traits)
    // ------------------------------------------------------------------

    public function testChartRank0DefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getChartRank0());
    }

    public function testSetAndGetChartRank0(): void
    {
        $this->player->setChartRank0(10);
        $this->assertSame(10, $this->player->getChartRank0());
    }

    public function testChartRank1DefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getChartRank1());
    }

    public function testSetAndGetChartRank1(): void
    {
        $this->player->setChartRank1(20);
        $this->assertSame(20, $this->player->getChartRank1());
    }

    public function testChartRank2DefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getChartRank2());
    }

    public function testSetAndGetChartRank2(): void
    {
        $this->player->setChartRank2(30);
        $this->assertSame(30, $this->player->getChartRank2());
    }

    public function testChartRank3DefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getChartRank3());
    }

    public function testSetAndGetChartRank3(): void
    {
        $this->player->setChartRank3(40);
        $this->assertSame(40, $this->player->getChartRank3());
    }

    public function testChartRank4DefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getChartRank4());
    }

    public function testSetAndGetChartRank4(): void
    {
        $this->player->setChartRank4(50);
        $this->assertSame(50, $this->player->getChartRank4());
    }

    public function testChartRank5DefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getChartRank5());
    }

    public function testSetAndGetChartRank5(): void
    {
        $this->player->setChartRank5(60);
        $this->assertSame(60, $this->player->getChartRank5());
    }

    // ------------------------------------------------------------------
    // chartRank6 – chartRank30 (inline properties)
    // ------------------------------------------------------------------

    public function testChartRank6DefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getChartRank(6));
    }

    public function testSetAndGetChartRankDynamic(): void
    {
        $this->player->setChartRank(6, 70);
        $this->assertSame(70, $this->player->getChartRank(6));
    }

    public function testSetChartRankReturnsSelf(): void
    {
        $result = $this->player->setChartRank(10, 99);
        $this->assertSame($this->player, $result);
    }

    public function testSetAndGetChartRankForMultipleRanks(): void
    {
        for ($rank = 6; $rank <= 30; $rank++) {
            $this->player->setChartRank($rank, $rank * 10);
        }
        for ($rank = 6; $rank <= 30; $rank++) {
            $this->assertSame($rank * 10, $this->player->getChartRank($rank));
        }
    }

    public function testChartRank30DefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getChartRank(30));
    }

    // ------------------------------------------------------------------
    // PointChartTrait
    // ------------------------------------------------------------------

    public function testPointChartDefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getPointChart());
    }

    public function testSetAndGetPointChart(): void
    {
        $this->player->setPointChart(1500);
        $this->assertSame(1500, $this->player->getPointChart());
    }

    // ------------------------------------------------------------------
    // RankPointChartTrait
    // ------------------------------------------------------------------

    public function testRankPointChartDefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getRankPointChart());
    }

    public function testSetAndGetRankPointChart(): void
    {
        $this->player->setRankPointChart(5);
        $this->assertSame(5, $this->player->getRankPointChart());
    }

    // ------------------------------------------------------------------
    // RankMedalTrait
    // ------------------------------------------------------------------

    public function testRankMedalDefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getRankMedal());
    }

    public function testSetAndGetRankMedal(): void
    {
        $this->player->setRankMedal(3);
        $this->assertSame(3, $this->player->getRankMedal());
    }

    // ------------------------------------------------------------------
    // NbChartTrait
    // ------------------------------------------------------------------

    public function testNbChartDefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getNbChart());
    }

    public function testSetAndGetNbChart(): void
    {
        $this->player->setNbChart(250);
        $this->assertSame(250, $this->player->getNbChart());
    }

    // ------------------------------------------------------------------
    // PointGameTrait
    // ------------------------------------------------------------------

    public function testPointGameDefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getPointGame());
    }

    public function testSetAndGetPointGame(): void
    {
        $this->player->setPointGame(3000);
        $this->assertSame(3000, $this->player->getPointGame());
    }

    // ------------------------------------------------------------------
    // RankPointGameTrait
    // ------------------------------------------------------------------

    public function testRankPointGameDefaultsToZero(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setDate('2025-01-01');
        $this->assertSame(0, $player->getRankPointGame());
    }

    public function testSetAndGetRankPointGame(): void
    {
        $this->player->setRankPointGame(8);
        $this->assertSame(8, $this->player->getRankPointGame());
    }
}
