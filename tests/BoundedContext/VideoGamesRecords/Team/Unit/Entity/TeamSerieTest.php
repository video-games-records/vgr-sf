<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Team\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamSerie;
use PHPUnit\Framework\TestCase;

class TeamSerieTest extends TestCase
{
    private TeamSerie $teamSerie;

    protected function setUp(): void
    {
        $this->teamSerie = new TeamSerie();
    }

    // ------------------------------------------------------------------
    // Team relation
    // ------------------------------------------------------------------

    public function testSetAndGetTeam(): void
    {
        $team = $this->createMock(Team::class);
        $this->teamSerie->setTeam($team);
        $this->assertSame($team, $this->teamSerie->getTeam());
    }

    public function testSetTeamReturnsStatic(): void
    {
        $team = $this->createMock(Team::class);
        $result = $this->teamSerie->setTeam($team);
        $this->assertSame($this->teamSerie, $result);
    }

    // ------------------------------------------------------------------
    // Serie relation
    // ------------------------------------------------------------------

    public function testSetAndGetSerie(): void
    {
        $serie = $this->createMock(Serie::class);
        $this->teamSerie->setSerie($serie);
        $this->assertSame($serie, $this->teamSerie->getSerie());
    }

    public function testSetSerieReturnsStatic(): void
    {
        $serie = $this->createMock(Serie::class);
        $result = $this->teamSerie->setSerie($serie);
        $this->assertSame($this->teamSerie, $result);
    }

    // ------------------------------------------------------------------
    // Trait properties (default values)
    // ------------------------------------------------------------------

    public function testNbEqualDefaultsToOne(): void
    {
        $this->assertSame(1, $this->teamSerie->getNbEqual());
    }

    public function testSetAndGetNbEqual(): void
    {
        $this->teamSerie->setNbEqual(3);
        $this->assertSame(3, $this->teamSerie->getNbEqual());
    }

    public function testRankPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamSerie->getRankPointChart());
    }

    public function testSetAndGetRankPointChart(): void
    {
        $this->teamSerie->setRankPointChart(8);
        $this->assertSame(8, $this->teamSerie->getRankPointChart());
    }

    public function testPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamSerie->getPointChart());
    }

    public function testSetAndGetPointChart(): void
    {
        $this->teamSerie->setPointChart(250);
        $this->assertSame(250, $this->teamSerie->getPointChart());
    }

    public function testPointGameDefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamSerie->getPointGame());
    }

    public function testSetAndGetPointGame(): void
    {
        $this->teamSerie->setPointGame(100);
        $this->assertSame(100, $this->teamSerie->getPointGame());
    }

    public function testRankMedalDefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamSerie->getRankMedal());
    }

    public function testSetAndGetRankMedal(): void
    {
        $this->teamSerie->setRankMedal(3);
        $this->assertSame(3, $this->teamSerie->getRankMedal());
    }

    public function testChartRank0DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamSerie->getChartRank0());
    }

    public function testSetAndGetChartRank0(): void
    {
        $this->teamSerie->setChartRank0(7);
        $this->assertSame(7, $this->teamSerie->getChartRank0());
    }

    public function testChartRank1DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamSerie->getChartRank1());
    }

    public function testChartRank2DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamSerie->getChartRank2());
    }

    public function testChartRank3DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamSerie->getChartRank3());
    }
}
