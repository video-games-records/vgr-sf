<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGame;
use DateTime;
use PHPUnit\Framework\TestCase;

class PlayerGameTest extends TestCase
{
    private PlayerGame $playerGame;

    protected function setUp(): void
    {
        $this->playerGame = new PlayerGame();
    }

    // ------------------------------------------------------------------
    // Basic properties (trait defaults)
    // ------------------------------------------------------------------

    public function testNbChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerGame->getNbChart());
    }

    public function testSetAndGetNbChart(): void
    {
        $this->playerGame->setNbChart(20);
        $this->assertSame(20, $this->playerGame->getNbChart());
    }

    public function testNbChartProvenDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerGame->getNbChartProven());
    }

    public function testSetAndGetNbChartProven(): void
    {
        $this->playerGame->setNbChartProven(10);
        $this->assertSame(10, $this->playerGame->getNbChartProven());
    }

    public function testNbEqualDefaultsToOne(): void
    {
        $this->assertSame(1, $this->playerGame->getNbEqual());
    }

    public function testSetAndGetNbEqual(): void
    {
        $this->playerGame->setNbEqual(3);
        $this->assertSame(3, $this->playerGame->getNbEqual());
    }

    public function testRankMedalDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerGame->getRankMedal());
    }

    public function testSetAndGetRankMedal(): void
    {
        $this->playerGame->setRankMedal(2);
        $this->assertSame(2, $this->playerGame->getRankMedal());
    }

    public function testChartRank0DefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerGame->getChartRank0());
    }

    public function testSetAndGetChartRank0(): void
    {
        $this->playerGame->setChartRank0(5);
        $this->assertSame(5, $this->playerGame->getChartRank0());
    }

    public function testRankPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerGame->getRankPointChart());
    }

    public function testSetAndGetRankPointChart(): void
    {
        $this->playerGame->setRankPointChart(100);
        $this->assertSame(100, $this->playerGame->getRankPointChart());
    }

    public function testPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerGame->getPointChart());
    }

    public function testSetAndGetPointChart(): void
    {
        $this->playerGame->setPointChart(500);
        $this->assertSame(500, $this->playerGame->getPointChart());
    }

    // ------------------------------------------------------------------
    // Own properties
    // ------------------------------------------------------------------

    public function testPointChartWithoutDlcDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerGame->getPointChartWithoutDlc());
    }

    public function testSetAndGetPointChartWithoutDlc(): void
    {
        $result = $this->playerGame->setPointChartWithoutDlc(250);
        $this->assertSame(250, $this->playerGame->getPointChartWithoutDlc());
        $this->assertSame($this->playerGame, $result);
    }

    public function testPointGameDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerGame->getPointGame());
    }

    public function testSetAndGetPointGame(): void
    {
        $result = $this->playerGame->setPointGame(1000);
        $this->assertSame(1000, $this->playerGame->getPointGame());
        $this->assertSame($this->playerGame, $result);
    }

    public function testSetAndGetLastUpdate(): void
    {
        $date = new DateTime('2024-03-01');
        $result = $this->playerGame->setLastUpdate($date);
        $this->assertSame($date, $this->playerGame->getLastUpdate());
        $this->assertSame($this->playerGame, $result);
    }

    public function testSetAndGetStatuses(): void
    {
        $statuses = ['none' => 5, 'proved' => 3];
        $result = $this->playerGame->setStatuses($statuses);
        $this->assertSame($statuses, $this->playerGame->getStatuses());
        $this->assertSame($this->playerGame, $result);
    }

    // ------------------------------------------------------------------
    // Player relation
    // ------------------------------------------------------------------

    public function testSetAndGetPlayer(): void
    {
        $player = $this->createMock(Player::class);
        $this->playerGame->setPlayer($player);
        $this->assertSame($player, $this->playerGame->getPlayer());
    }

    // ------------------------------------------------------------------
    // Game relation
    // ------------------------------------------------------------------

    public function testSetAndGetGame(): void
    {
        $game = $this->createMock(Game::class);
        $this->playerGame->setGame($game);
        $this->assertSame($game, $this->playerGame->getGame());
    }

    // ------------------------------------------------------------------
    // getId composite key
    // ------------------------------------------------------------------

    public function testGetIdReturnsCompositeKey(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getId')->willReturn(1);

        $game = $this->createMock(Game::class);
        $game->method('getId')->willReturn(42);

        $this->playerGame->setPlayer($player);
        $this->playerGame->setGame($game);

        $this->assertSame('player=1;game=42', $this->playerGame->getId());
    }
}
