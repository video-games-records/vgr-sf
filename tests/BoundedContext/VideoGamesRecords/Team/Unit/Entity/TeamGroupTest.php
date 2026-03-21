<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Team\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamGroup;
use PHPUnit\Framework\TestCase;

class TeamGroupTest extends TestCase
{
    private TeamGroup $teamGroup;

    protected function setUp(): void
    {
        $this->teamGroup = new TeamGroup();
    }

    // ------------------------------------------------------------------
    // Team relation
    // ------------------------------------------------------------------

    public function testSetAndGetTeam(): void
    {
        $team = $this->createMock(Team::class);
        $this->teamGroup->setTeam($team);
        $this->assertSame($team, $this->teamGroup->getTeam());
    }

    public function testSetTeamReturnsStatic(): void
    {
        $team = $this->createMock(Team::class);
        $result = $this->teamGroup->setTeam($team);
        $this->assertSame($this->teamGroup, $result);
    }

    // ------------------------------------------------------------------
    // Group relation
    // ------------------------------------------------------------------

    public function testSetAndGetGroup(): void
    {
        $group = $this->createMock(Group::class);
        $this->teamGroup->setGroup($group);
        $this->assertSame($group, $this->teamGroup->getGroup());
    }

    public function testSetGroupReturnsStatic(): void
    {
        $group = $this->createMock(Group::class);
        $result = $this->teamGroup->setGroup($group);
        $this->assertSame($this->teamGroup, $result);
    }

    // ------------------------------------------------------------------
    // Composite ID
    // ------------------------------------------------------------------

    public function testGetIdReturnsCompositeString(): void
    {
        $team = $this->createMock(Team::class);
        $team->method('getId')->willReturn(2);

        $group = $this->createMock(Group::class);
        $group->method('getId')->willReturn(9);

        $this->teamGroup->setTeam($team);
        $this->teamGroup->setGroup($group);

        $this->assertSame('team=2;group=9', $this->teamGroup->getId());
    }

    // ------------------------------------------------------------------
    // Trait properties (default values)
    // ------------------------------------------------------------------

    public function testRankPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGroup->getRankPointChart());
    }

    public function testSetAndGetRankPointChart(): void
    {
        $this->teamGroup->setRankPointChart(6);
        $this->assertSame(6, $this->teamGroup->getRankPointChart());
    }

    public function testPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGroup->getPointChart());
    }

    public function testSetAndGetPointChart(): void
    {
        $this->teamGroup->setPointChart(400);
        $this->assertSame(400, $this->teamGroup->getPointChart());
    }

    public function testRankMedalDefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGroup->getRankMedal());
    }

    public function testSetAndGetRankMedal(): void
    {
        $this->teamGroup->setRankMedal(1);
        $this->assertSame(1, $this->teamGroup->getRankMedal());
    }

    public function testChartRank0DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGroup->getChartRank0());
    }

    public function testSetAndGetChartRank0(): void
    {
        $this->teamGroup->setChartRank0(11);
        $this->assertSame(11, $this->teamGroup->getChartRank0());
    }

    public function testChartRank1DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGroup->getChartRank1());
    }

    public function testChartRank2DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGroup->getChartRank2());
    }

    public function testChartRank3DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGroup->getChartRank3());
    }
}
