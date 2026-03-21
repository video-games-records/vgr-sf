<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\GroupOrderBy;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    private Group $group;

    protected function setUp(): void
    {
        $this->group = new Group();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyCharts(): void
    {
        $group = new Group();
        $this->assertInstanceOf(Collection::class, $group->getCharts());
        $this->assertCount(0, $group->getCharts());
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->group->getId());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->group->setId(5);
        $this->assertSame(5, $this->group->getId());
        $this->assertSame($this->group, $result);
    }

    public function testLibGroupEnDefaultsToEmptyString(): void
    {
        $this->assertSame('', $this->group->getLibGroupEn());
    }

    public function testSetAndGetLibGroupEn(): void
    {
        $result = $this->group->setLibGroupEn('Arcade Mode');
        $this->assertSame('Arcade Mode', $this->group->getLibGroupEn());
        $this->assertSame($this->group, $result);
    }

    public function testLibGroupFrDefaultsToEmptyString(): void
    {
        $this->assertSame('', $this->group->getLibGroupFr());
    }

    public function testSetAndGetLibGroupFr(): void
    {
        $result = $this->group->setLibGroupFr('Mode Arcade');
        $this->assertSame('Mode Arcade', $this->group->getLibGroupFr());
        $this->assertSame($this->group, $result);
    }

    public function testSetLibGroupFrWithNullDoesNotChange(): void
    {
        $this->group->setLibGroupFr('Mode Arcade');
        $this->group->setLibGroupFr(null);
        $this->assertSame('Mode Arcade', $this->group->getLibGroupFr());
    }

    public function testOrderByDefaultsToName(): void
    {
        $this->assertSame(GroupOrderBy::NAME, $this->group->getOrderBy());
    }

    public function testSetAndGetOrderBy(): void
    {
        $result = $this->group->setOrderBy(GroupOrderBy::ID);
        $this->assertSame(GroupOrderBy::ID, $this->group->getOrderBy());
        $this->assertSame($this->group, $result);
    }

    public function testGetGroupOrderByReturnsValueObject(): void
    {
        $this->group->setOrderBy(GroupOrderBy::CUSTOM);
        $orderBy = $this->group->getGroupOrderBy();
        $this->assertInstanceOf(GroupOrderBy::class, $orderBy);
        $this->assertSame(GroupOrderBy::CUSTOM, $orderBy->getValue());
    }

    // ------------------------------------------------------------------
    // Trait defaults
    // ------------------------------------------------------------------

    public function testNbChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->group->getNbChart());
    }

    public function testNbPostDefaultsToZero(): void
    {
        $this->assertSame(0, $this->group->getNbPost());
    }

    public function testNbPlayerDefaultsToZero(): void
    {
        $this->assertSame(0, $this->group->getNbPlayer());
    }

    public function testIsDlcDefaultsToFalse(): void
    {
        $this->assertFalse($this->group->getIsDlc());
    }

    // ------------------------------------------------------------------
    // Game relation
    // ------------------------------------------------------------------

    public function testSetAndGetGame(): void
    {
        $game = $this->createMock(Game::class);
        $result = $this->group->setGame($game);
        $this->assertSame($game, $this->group->getGame());
        $this->assertSame($this->group, $result);
    }

    // ------------------------------------------------------------------
    // Charts collection
    // ------------------------------------------------------------------

    public function testAddChart(): void
    {
        $chart = $this->createMock(Chart::class);
        $chart->expects($this->once())->method('setGroup')->with($this->group);

        $this->group->addChart($chart);

        $this->assertCount(1, $this->group->getCharts());
    }

    public function testRemoveChart(): void
    {
        $chart = new Chart();
        $this->group->addChart($chart);
        $this->group->removeChart($chart);

        $this->assertCount(0, $this->group->getCharts());
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testGetDefaultName(): void
    {
        $this->group->setLibGroupEn('Story Mode');
        $this->assertSame('Story Mode', $this->group->getDefaultName());
    }

    public function testToString(): void
    {
        $this->group->setId(3);
        $this->group->setLibGroupEn('Story Mode');
        $this->assertSame('Story Mode [3]', (string) $this->group);
    }
}
