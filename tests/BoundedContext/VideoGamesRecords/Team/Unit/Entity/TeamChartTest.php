<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Team\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamChart;
use PHPUnit\Framework\TestCase;

class TeamChartTest extends TestCase
{
    private TeamChart $teamChart;

    protected function setUp(): void
    {
        $this->teamChart = new TeamChart();
    }

    // ------------------------------------------------------------------
    // Team relation
    // ------------------------------------------------------------------

    public function testSetAndGetTeam(): void
    {
        $team = $this->createMock(Team::class);
        $this->teamChart->setTeam($team);
        $this->assertSame($team, $this->teamChart->getTeam());
    }

    public function testSetTeamReturnsStatic(): void
    {
        $team = $this->createMock(Team::class);
        $result = $this->teamChart->setTeam($team);
        $this->assertSame($this->teamChart, $result);
    }

    // ------------------------------------------------------------------
    // Chart relation
    // ------------------------------------------------------------------

    public function testSetAndGetChart(): void
    {
        $chart = $this->createMock(Chart::class);
        $this->teamChart->setChart($chart);
        $this->assertSame($chart, $this->teamChart->getChart());
    }

    public function testSetChartReturnsStatic(): void
    {
        $chart = $this->createMock(Chart::class);
        $result = $this->teamChart->setChart($chart);
        $this->assertSame($this->teamChart, $result);
    }

    // ------------------------------------------------------------------
    // Trait properties (default values)
    // ------------------------------------------------------------------

    public function testRankPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamChart->getRankPointChart());
    }

    public function testSetAndGetRankPointChart(): void
    {
        $this->teamChart->setRankPointChart(5);
        $this->assertSame(5, $this->teamChart->getRankPointChart());
    }

    public function testPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamChart->getPointChart());
    }

    public function testSetAndGetPointChart(): void
    {
        $this->teamChart->setPointChart(200);
        $this->assertSame(200, $this->teamChart->getPointChart());
    }

    public function testChartRank0DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamChart->getChartRank0());
    }

    public function testSetAndGetChartRank0(): void
    {
        $this->teamChart->setChartRank0(10);
        $this->assertSame(10, $this->teamChart->getChartRank0());
    }

    public function testChartRank1DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamChart->getChartRank1());
    }

    public function testSetAndGetChartRank1(): void
    {
        $this->teamChart->setChartRank1(8);
        $this->assertSame(8, $this->teamChart->getChartRank1());
    }

    public function testChartRank2DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamChart->getChartRank2());
    }

    public function testSetAndGetChartRank2(): void
    {
        $this->teamChart->setChartRank2(6);
        $this->assertSame(6, $this->teamChart->getChartRank2());
    }

    public function testChartRank3DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamChart->getChartRank3());
    }

    public function testSetAndGetChartRank3(): void
    {
        $this->teamChart->setChartRank3(4);
        $this->assertSame(4, $this->teamChart->getChartRank3());
    }
}
