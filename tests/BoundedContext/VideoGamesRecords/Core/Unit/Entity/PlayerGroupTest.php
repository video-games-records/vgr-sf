<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGroup;
use PHPUnit\Framework\TestCase;

class PlayerGroupTest extends TestCase
{
    private PlayerGroup $playerGroup;

    protected function setUp(): void
    {
        $this->playerGroup = new PlayerGroup();
    }

    // ------------------------------------------------------------------
    // Basic properties (trait defaults)
    // ------------------------------------------------------------------

    public function testNbChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerGroup->getNbChart());
    }

    public function testSetAndGetNbChart(): void
    {
        $this->playerGroup->setNbChart(8);
        $this->assertSame(8, $this->playerGroup->getNbChart());
    }

    public function testNbChartProvenDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerGroup->getNbChartProven());
    }

    public function testSetAndGetNbChartProven(): void
    {
        $this->playerGroup->setNbChartProven(4);
        $this->assertSame(4, $this->playerGroup->getNbChartProven());
    }

    public function testRankMedalDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerGroup->getRankMedal());
    }

    public function testSetAndGetRankMedal(): void
    {
        $this->playerGroup->setRankMedal(1);
        $this->assertSame(1, $this->playerGroup->getRankMedal());
    }

    public function testChartRank0DefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerGroup->getChartRank0());
    }

    public function testRankPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerGroup->getRankPointChart());
    }

    public function testPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerGroup->getPointChart());
    }

    public function testSetAndGetPointChart(): void
    {
        $this->playerGroup->setPointChart(300);
        $this->assertSame(300, $this->playerGroup->getPointChart());
    }

    // ------------------------------------------------------------------
    // Player relation
    // ------------------------------------------------------------------

    public function testSetAndGetPlayer(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->playerGroup->setPlayer($player);
        $this->assertSame($player, $this->playerGroup->getPlayer());
        $this->assertSame($this->playerGroup, $result);
    }

    // ------------------------------------------------------------------
    // Group relation
    // ------------------------------------------------------------------

    public function testSetAndGetGroup(): void
    {
        $group = $this->createMock(Group::class);
        $result = $this->playerGroup->setGroup($group);
        $this->assertSame($group, $this->playerGroup->getGroup());
        $this->assertSame($this->playerGroup, $result);
    }

    // ------------------------------------------------------------------
    // getId composite key
    // ------------------------------------------------------------------

    public function testGetIdReturnsCompositeKey(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getId')->willReturn(2);

        $group = $this->createMock(Group::class);
        $group->method('getId')->willReturn(7);

        $this->playerGroup->setPlayer($player);
        $this->playerGroup->setGroup($group);

        $this->assertSame('player=2;group=7', $this->playerGroup->getId());
    }
}
