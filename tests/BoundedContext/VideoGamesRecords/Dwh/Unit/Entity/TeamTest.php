<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Dwh\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Team;
use PHPUnit\Framework\TestCase;

class TeamTest extends TestCase
{
    private Team $team;

    protected function setUp(): void
    {
        $this->team = new Team();
        $this->team->setId(1);
        $this->team->setDate('2025-01-01');
    }

    // ------------------------------------------------------------------
    // id
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $this->team->setId(42);
        $this->assertSame(42, $this->team->getId());
    }

    public function testSetIdReturnsSelf(): void
    {
        $result = $this->team->setId(10);
        $this->assertSame($this->team, $result);
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->team->setId(7);
        $this->assertSame('7 [7]', (string) $this->team);
    }

    // ------------------------------------------------------------------
    // DateTrait
    // ------------------------------------------------------------------

    public function testSetAndGetDate(): void
    {
        $this->team->setDate('2025-06-15');
        $this->assertSame('2025-06-15', $this->team->getDate());
    }

    public function testSetDateReturnsSelf(): void
    {
        $result = $this->team->setDate('2025-01-01');
        $this->assertSame($this->team, $result);
    }

    // ------------------------------------------------------------------
    // NbPostDayTrait
    // ------------------------------------------------------------------

    public function testNbPostDayDefaultsToZero(): void
    {
        $team = new Team();
        $team->setId(1);
        $team->setDate('2025-01-01');
        $this->assertSame(0, $team->getNbPostDay());
    }

    public function testSetAndGetNbPostDay(): void
    {
        $this->team->setNbPostDay(5);
        $this->assertSame(5, $this->team->getNbPostDay());
    }

    public function testSetNbPostDayReturnsSelf(): void
    {
        $result = $this->team->setNbPostDay(3);
        $this->assertSame($this->team, $result);
    }

    // ------------------------------------------------------------------
    // ChartRank0Trait – ChartRank3Trait (from shared traits)
    // ------------------------------------------------------------------

    public function testChartRank0DefaultsToZero(): void
    {
        $team = new Team();
        $team->setId(1);
        $team->setDate('2025-01-01');
        $this->assertSame(0, $team->getChartRank0());
    }

    public function testSetAndGetChartRank0(): void
    {
        $this->team->setChartRank0(10);
        $this->assertSame(10, $this->team->getChartRank0());
    }

    public function testChartRank1DefaultsToZero(): void
    {
        $team = new Team();
        $team->setId(1);
        $team->setDate('2025-01-01');
        $this->assertSame(0, $team->getChartRank1());
    }

    public function testSetAndGetChartRank1(): void
    {
        $this->team->setChartRank1(20);
        $this->assertSame(20, $this->team->getChartRank1());
    }

    public function testChartRank2DefaultsToZero(): void
    {
        $team = new Team();
        $team->setId(1);
        $team->setDate('2025-01-01');
        $this->assertSame(0, $team->getChartRank2());
    }

    public function testSetAndGetChartRank2(): void
    {
        $this->team->setChartRank2(30);
        $this->assertSame(30, $this->team->getChartRank2());
    }

    public function testChartRank3DefaultsToZero(): void
    {
        $team = new Team();
        $team->setId(1);
        $team->setDate('2025-01-01');
        $this->assertSame(0, $team->getChartRank3());
    }

    public function testSetAndGetChartRank3(): void
    {
        $this->team->setChartRank3(40);
        $this->assertSame(40, $this->team->getChartRank3());
    }

    // ------------------------------------------------------------------
    // PointChartTrait
    // ------------------------------------------------------------------

    public function testPointChartDefaultsToZero(): void
    {
        $team = new Team();
        $team->setId(1);
        $team->setDate('2025-01-01');
        $this->assertSame(0, $team->getPointChart());
    }

    public function testSetAndGetPointChart(): void
    {
        $this->team->setPointChart(1500);
        $this->assertSame(1500, $this->team->getPointChart());
    }

    // ------------------------------------------------------------------
    // RankPointChartTrait
    // ------------------------------------------------------------------

    public function testRankPointChartDefaultsToZero(): void
    {
        $team = new Team();
        $team->setId(1);
        $team->setDate('2025-01-01');
        $this->assertSame(0, $team->getRankPointChart());
    }

    public function testSetAndGetRankPointChart(): void
    {
        $this->team->setRankPointChart(5);
        $this->assertSame(5, $this->team->getRankPointChart());
    }

    // ------------------------------------------------------------------
    // RankMedalTrait
    // ------------------------------------------------------------------

    public function testRankMedalDefaultsToZero(): void
    {
        $team = new Team();
        $team->setId(1);
        $team->setDate('2025-01-01');
        $this->assertSame(0, $team->getRankMedal());
    }

    public function testSetAndGetRankMedal(): void
    {
        $this->team->setRankMedal(3);
        $this->assertSame(3, $this->team->getRankMedal());
    }

    // ------------------------------------------------------------------
    // RankPointBadgeTrait
    // ------------------------------------------------------------------

    public function testRankBadgeDefaultsToZero(): void
    {
        $team = new Team();
        $team->setId(1);
        $team->setDate('2025-01-01');
        $this->assertSame(0, $team->getRankBadge());
    }

    public function testSetAndGetRankBadge(): void
    {
        $this->team->setRankBadge(12);
        $this->assertSame(12, $this->team->getRankBadge());
    }

    // ------------------------------------------------------------------
    // PointBadgeTrait
    // ------------------------------------------------------------------

    public function testPointBadgeDefaultsToZero(): void
    {
        $team = new Team();
        $team->setId(1);
        $team->setDate('2025-01-01');
        $this->assertSame(0, $team->getPointBadge());
    }

    public function testSetAndGetPointBadge(): void
    {
        $this->team->setPointBadge(750);
        $this->assertSame(750, $this->team->getPointBadge());
    }

    // ------------------------------------------------------------------
    // NbMasterBadgeTrait
    // ------------------------------------------------------------------

    public function testNbMasterBadgeDefaultsToZero(): void
    {
        $team = new Team();
        $team->setId(1);
        $team->setDate('2025-01-01');
        $this->assertSame(0, $team->getNbMasterBadge());
    }

    public function testSetAndGetNbMasterBadge(): void
    {
        $this->team->setNbMasterBadge(15);
        $this->assertSame(15, $this->team->getNbMasterBadge());
    }

    // ------------------------------------------------------------------
    // PointGameTrait
    // ------------------------------------------------------------------

    public function testPointGameDefaultsToZero(): void
    {
        $team = new Team();
        $team->setId(1);
        $team->setDate('2025-01-01');
        $this->assertSame(0, $team->getPointGame());
    }

    public function testSetAndGetPointGame(): void
    {
        $this->team->setPointGame(3000);
        $this->assertSame(3000, $this->team->getPointGame());
    }

    // ------------------------------------------------------------------
    // RankPointGameTrait
    // ------------------------------------------------------------------

    public function testRankPointGameDefaultsToZero(): void
    {
        $team = new Team();
        $team->setId(1);
        $team->setDate('2025-01-01');
        $this->assertSame(0, $team->getRankPointGame());
    }

    public function testSetAndGetRankPointGame(): void
    {
        $this->team->setRankPointGame(8);
        $this->assertSame(8, $this->team->getRankPointGame());
    }

    // ------------------------------------------------------------------
    // setFromArray
    // ------------------------------------------------------------------

    public function testSetFromArray(): void
    {
        $this->team->setFromArray([
            'pointChart' => 500,
            'rankMedal' => 2,
            'nbMasterBadge' => 5,
        ]);
        $this->assertSame(500, $this->team->getPointChart());
        $this->assertSame(2, $this->team->getRankMedal());
        $this->assertSame(5, $this->team->getNbMasterBadge());
    }

    public function testSetFromArrayReturnsSelf(): void
    {
        $result = $this->team->setFromArray([]);
        $this->assertSame($this->team, $result);
    }
}
