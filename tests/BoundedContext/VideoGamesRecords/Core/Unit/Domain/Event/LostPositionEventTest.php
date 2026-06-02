<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Domain\Event;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Event\LostPositionEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class LostPositionEventTest extends TestCase
{
    public function testExtendsEvent(): void
    {
        $event = new LostPositionEvent($this->createMock(PlayerChart::class), 1, 0);
        $this->assertInstanceOf(Event::class, $event);
    }

    public function testGetPlayerChartReturnsInjectedEntity(): void
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $event = new LostPositionEvent($playerChart, 3, 1);

        $this->assertSame($playerChart, $event->getPlayerChart());
    }

    public function testGetPreviousRank(): void
    {
        $event = new LostPositionEvent($this->createMock(PlayerChart::class), 5, 2);
        $this->assertSame(5, $event->getPreviousRank());
    }

    public function testGetPreviousNbEqual(): void
    {
        $event = new LostPositionEvent($this->createMock(PlayerChart::class), 5, 2);
        $this->assertSame(2, $event->getPreviousNbEqual());
    }

    public function testGetCurrentRankDelegatesToPlayerChart(): void
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->method('getRank')->willReturn(10);

        $event = new LostPositionEvent($playerChart, 3, 0);

        $this->assertSame(10, $event->getCurrentRank());
    }

    public function testHasLostPositionReturnsTrueWhenCurrentRankIsHigherThanPrevious(): void
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->method('getRank')->willReturn(5);

        $event = new LostPositionEvent($playerChart, 3, 0);

        $this->assertTrue($event->hasLostPosition());
    }

    public function testHasLostPositionReturnsFalseWhenCurrentRankIsLowerThanPrevious(): void
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->method('getRank')->willReturn(1);

        $event = new LostPositionEvent($playerChart, 3, 0);

        $this->assertFalse($event->hasLostPosition());
    }

    public function testHasLostPositionReturnsFalseWhenRankUnchanged(): void
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->method('getRank')->willReturn(3);

        $event = new LostPositionEvent($playerChart, 3, 0);

        $this->assertFalse($event->hasLostPosition());
    }
}
