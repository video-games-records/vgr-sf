<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Dwh\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Game;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    private Game $game;

    protected function setUp(): void
    {
        $this->game = new Game();
        $this->game->setId(1);
        $this->game->setDate('2025-01-01');
    }

    // ------------------------------------------------------------------
    // id
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $this->game->setId(42);
        $this->assertSame(42, $this->game->getId());
    }

    public function testSetIdReturnsSelf(): void
    {
        $result = $this->game->setId(10);
        $this->assertSame($this->game, $result);
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->game->setId(7);
        $this->assertSame('7 [7]', (string) $this->game);
    }

    // ------------------------------------------------------------------
    // DateTrait
    // ------------------------------------------------------------------

    public function testSetAndGetDate(): void
    {
        $this->game->setDate('2025-06-15');
        $this->assertSame('2025-06-15', $this->game->getDate());
    }

    public function testSetDateReturnsSelf(): void
    {
        $result = $this->game->setDate('2025-01-01');
        $this->assertSame($this->game, $result);
    }

    // ------------------------------------------------------------------
    // NbPostDayTrait
    // ------------------------------------------------------------------

    public function testNbPostDayDefaultsToZero(): void
    {
        $game = new Game();
        $game->setId(1);
        $game->setDate('2025-01-01');
        $this->assertSame(0, $game->getNbPostDay());
    }

    public function testSetAndGetNbPostDay(): void
    {
        $this->game->setNbPostDay(5);
        $this->assertSame(5, $this->game->getNbPostDay());
    }

    public function testSetNbPostDayReturnsSelf(): void
    {
        $result = $this->game->setNbPostDay(3);
        $this->assertSame($this->game, $result);
    }

    // ------------------------------------------------------------------
    // NbPostTrait
    // ------------------------------------------------------------------

    public function testNbPostDefaultsToZero(): void
    {
        $game = new Game();
        $game->setId(1);
        $game->setDate('2025-01-01');
        $this->assertSame(0, $game->getNbPost());
    }

    public function testSetAndGetNbPost(): void
    {
        $this->game->setNbPost(100);
        $this->assertSame(100, $this->game->getNbPost());
    }

    public function testSetNbPostReturnsSelf(): void
    {
        $result = $this->game->setNbPost(50);
        $this->assertSame($this->game, $result);
    }

    // ------------------------------------------------------------------
    // setFromArray
    // ------------------------------------------------------------------

    public function testSetFromArray(): void
    {
        $this->game->setFromArray([
            'nbPost' => 200,
            'nbPostDay' => 10,
        ]);
        $this->assertSame(200, $this->game->getNbPost());
        $this->assertSame(10, $this->game->getNbPostDay());
    }

    public function testSetFromArrayReturnsSelf(): void
    {
        $result = $this->game->setFromArray([]);
        $this->assertSame($this->game, $result);
    }
}
