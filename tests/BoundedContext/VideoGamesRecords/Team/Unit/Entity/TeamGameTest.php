<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Team\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamGame;
use PHPUnit\Framework\TestCase;

class TeamGameTest extends TestCase
{
    private TeamGame $teamGame;

    protected function setUp(): void
    {
        $this->teamGame = new TeamGame();
    }

    // ------------------------------------------------------------------
    // Team relation
    // ------------------------------------------------------------------

    public function testSetAndGetTeam(): void
    {
        $team = $this->createMock(Team::class);
        $this->teamGame->setTeam($team);
        $this->assertSame($team, $this->teamGame->getTeam());
    }

    public function testSetTeamReturnsStatic(): void
    {
        $team = $this->createMock(Team::class);
        $result = $this->teamGame->setTeam($team);
        $this->assertSame($this->teamGame, $result);
    }

    // ------------------------------------------------------------------
    // Game relation
    // ------------------------------------------------------------------

    public function testSetAndGetGame(): void
    {
        $game = $this->createMock(Game::class);
        $this->teamGame->setGame($game);
        $this->assertSame($game, $this->teamGame->getGame());
    }

    public function testSetGameReturnsStatic(): void
    {
        $game = $this->createMock(Game::class);
        $result = $this->teamGame->setGame($game);
        $this->assertSame($this->teamGame, $result);
    }

    // ------------------------------------------------------------------
    // Composite ID
    // ------------------------------------------------------------------

    public function testGetIdReturnsCompositeString(): void
    {
        $team = $this->createMock(Team::class);
        $team->method('getId')->willReturn(3);

        $game = $this->createMock(Game::class);
        $game->method('getId')->willReturn(7);

        $this->teamGame->setTeam($team);
        $this->teamGame->setGame($game);

        $this->assertSame('team=3;game=7', $this->teamGame->getId());
    }

    // ------------------------------------------------------------------
    // Trait properties (default values)
    // ------------------------------------------------------------------

    public function testNbEqualDefaultsToOne(): void
    {
        $this->assertSame(1, $this->teamGame->getNbEqual());
    }

    public function testSetAndGetNbEqual(): void
    {
        $this->teamGame->setNbEqual(5);
        $this->assertSame(5, $this->teamGame->getNbEqual());
    }

    public function testRankPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGame->getRankPointChart());
    }

    public function testSetAndGetRankPointChart(): void
    {
        $this->teamGame->setRankPointChart(12);
        $this->assertSame(12, $this->teamGame->getRankPointChart());
    }

    public function testPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGame->getPointChart());
    }

    public function testSetAndGetPointChart(): void
    {
        $this->teamGame->setPointChart(300);
        $this->assertSame(300, $this->teamGame->getPointChart());
    }

    public function testPointGameDefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGame->getPointGame());
    }

    public function testSetAndGetPointGame(): void
    {
        $this->teamGame->setPointGame(150);
        $this->assertSame(150, $this->teamGame->getPointGame());
    }

    public function testRankMedalDefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGame->getRankMedal());
    }

    public function testSetAndGetRankMedal(): void
    {
        $this->teamGame->setRankMedal(2);
        $this->assertSame(2, $this->teamGame->getRankMedal());
    }

    public function testChartRank0DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGame->getChartRank0());
    }

    public function testSetAndGetChartRank0(): void
    {
        $this->teamGame->setChartRank0(9);
        $this->assertSame(9, $this->teamGame->getChartRank0());
    }

    public function testChartRank1DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGame->getChartRank1());
    }

    public function testChartRank2DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGame->getChartRank2());
    }

    public function testChartRank3DefaultsToZero(): void
    {
        $this->assertSame(0, $this->teamGame->getChartRank3());
    }
}
