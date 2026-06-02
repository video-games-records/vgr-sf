<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Infrastructure\EventSubscriber;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\LostPosition;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Event\LostPositionEvent;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\EventSubscriber\LostPositionSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class LostPositionSubscriberTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private LostPositionSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->subscriber = new LostPositionSubscriber($this->em);
    }

    private function makePlayerChart(int $rank, int $nbEqual, int $playerId = 1, int $chartId = 10): PlayerChart&MockObject
    {
        $player = $this->createMock(Player::class);
        $player->method('getId')->willReturn($playerId);

        $chart = $this->createMock(Chart::class);
        $chart->method('getId')->willReturn($chartId);

        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->method('getRank')->willReturn($rank);
        $playerChart->method('getNbEqual')->willReturn($nbEqual);
        $playerChart->method('getPlayer')->willReturn($player);
        $playerChart->method('getChart')->willReturn($chart);

        return $playerChart;
    }

    public function testSubscribesToLostPositionEvent(): void
    {
        $events = LostPositionSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(LostPositionEvent::class, $events);
        $this->assertSame('handleLostPosition', $events[LostPositionEvent::class]);
    }

    public function testPersistsLostPositionWhenRankDropsFromTop3(): void
    {
        $playerChart = $this->makePlayerChart(rank: 2, nbEqual: 1);
        $event = new LostPositionEvent($playerChart, previousRank: 1, previousNbEqual: 1);

        $playerRef = $this->createMock(Player::class);
        $chartRef = $this->createMock(Chart::class);
        $this->em->method('getReference')->willReturnOnConsecutiveCalls($playerRef, $chartRef);
        $this->em->expects($this->once())->method('persist');

        $this->subscriber->handleLostPosition($event);
    }

    public function testDoesNotPersistWhenRankImproves(): void
    {
        $playerChart = $this->makePlayerChart(rank: 1, nbEqual: 1);
        $event = new LostPositionEvent($playerChart, previousRank: 3, previousNbEqual: 1);

        $this->em->expects($this->never())->method('persist');

        $this->subscriber->handleLostPosition($event);
    }

    public function testDoesNotPersistWhenRankUnchangedOutsideTop3(): void
    {
        $playerChart = $this->makePlayerChart(rank: 5, nbEqual: 1);
        $event = new LostPositionEvent($playerChart, previousRank: 5, previousNbEqual: 1);

        $this->em->expects($this->never())->method('persist');

        $this->subscriber->handleLostPosition($event);
    }

    public function testDoesNotPersistWhenDropsFromBeyondTop3(): void
    {
        $playerChart = $this->makePlayerChart(rank: 5, nbEqual: 1);
        $event = new LostPositionEvent($playerChart, previousRank: 4, previousNbEqual: 1);

        $this->em->expects($this->never())->method('persist');

        $this->subscriber->handleLostPosition($event);
    }

    public function testPersistsWhenRankOneIsSharedForFirstTime(): void
    {
        // Was alone at rank 1 (nbEqual=1), now tied (nbEqual>1) but still rank 1
        $playerChart = $this->makePlayerChart(rank: 1, nbEqual: 2);
        $event = new LostPositionEvent($playerChart, previousRank: 1, previousNbEqual: 1);

        $playerRef = $this->createMock(Player::class);
        $chartRef = $this->createMock(Chart::class);
        $this->em->method('getReference')->willReturnOnConsecutiveCalls($playerRef, $chartRef);
        $this->em->expects($this->once())->method('persist');

        $this->subscriber->handleLostPosition($event);
    }

    public function testDoesNotPersistWhenAlreadyTiedAtRankOne(): void
    {
        // Was already tied at rank 1, nothing changes
        $playerChart = $this->makePlayerChart(rank: 1, nbEqual: 2);
        $event = new LostPositionEvent($playerChart, previousRank: 1, previousNbEqual: 2);

        $this->em->expects($this->never())->method('persist');

        $this->subscriber->handleLostPosition($event);
    }

    public function testPersistsLostPositionWithOldRankZeroWhenWasAloneAtRankOne(): void
    {
        // Was alone at rank 1 (oldNbEqual==1 && oldRank==1) → stored oldRank = 0
        $playerChart = $this->makePlayerChart(rank: 1, nbEqual: 2);
        $event = new LostPositionEvent($playerChart, previousRank: 1, previousNbEqual: 1);

        $capturedLostPosition = null;
        $playerRef = $this->createMock(Player::class);
        $chartRef = $this->createMock(Chart::class);
        $this->em->method('getReference')->willReturnOnConsecutiveCalls($playerRef, $chartRef);
        $this->em->expects($this->once())->method('persist')
            ->willReturnCallback(function (LostPosition $entity) use (&$capturedLostPosition): void {
                $capturedLostPosition = $entity;
            });

        $this->subscriber->handleLostPosition($event);

        $this->assertInstanceOf(LostPosition::class, $capturedLostPosition);
        $this->assertSame(0, $capturedLostPosition->getOldRank());
    }
}
