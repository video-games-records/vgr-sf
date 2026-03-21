<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\TeamBadge;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use DateTime;
use PHPUnit\Framework\TestCase;

class TeamBadgeTest extends TestCase
{
    private TeamBadge $teamBadge;

    protected function setUp(): void
    {
        $this->teamBadge = new TeamBadge();
    }

    // ------------------------------------------------------------------
    // Default values
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->teamBadge->getId());
    }

    public function testEndedAtDefaultsToNull(): void
    {
        $this->assertNull($this->teamBadge->getEndedAt());
    }

    public function testMbOrderDefaultsToNull(): void
    {
        $this->assertNull($this->teamBadge->getMbOrder());
    }

    // ------------------------------------------------------------------
    // id
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $result = $this->teamBadge->setId(2);
        $this->assertSame(2, $this->teamBadge->getId());
        $this->assertSame($this->teamBadge, $result);
    }

    // ------------------------------------------------------------------
    // endedAt
    // ------------------------------------------------------------------

    public function testSetAndGetEndedAt(): void
    {
        $date = new DateTime('2026-03-21');
        $result = $this->teamBadge->setEndedAt($date);
        $this->assertSame($date, $this->teamBadge->getEndedAt());
        $this->assertSame($this->teamBadge, $result);
    }

    public function testSetEndedAtToNull(): void
    {
        $this->teamBadge->setEndedAt(new DateTime());
        $result = $this->teamBadge->setEndedAt(null);
        $this->assertNull($this->teamBadge->getEndedAt());
        $this->assertSame($this->teamBadge, $result);
    }

    // ------------------------------------------------------------------
    // mbOrder
    // ------------------------------------------------------------------

    public function testSetAndGetMbOrder(): void
    {
        $result = $this->teamBadge->setMbOrder(5);
        $this->assertSame(5, $this->teamBadge->getMbOrder());
        $this->assertSame($this->teamBadge, $result);
    }

    // ------------------------------------------------------------------
    // badge relation
    // ------------------------------------------------------------------

    public function testSetAndGetBadge(): void
    {
        $badge = $this->createMock(Badge::class);
        $result = $this->teamBadge->setBadge($badge);
        $this->assertSame($badge, $this->teamBadge->getBadge());
        $this->assertSame($this->teamBadge, $result);
    }

    // ------------------------------------------------------------------
    // team relation
    // ------------------------------------------------------------------

    public function testSetAndGetTeam(): void
    {
        $team = $this->createMock(Team::class);
        $result = $this->teamBadge->setTeam($team);
        $this->assertSame($team, $this->teamBadge->getTeam());
        $this->assertSame($this->teamBadge, $result);
    }

    // ------------------------------------------------------------------
    // TimestampableEntity (trait presence check)
    // ------------------------------------------------------------------

    public function testCreatedAtCanBeSet(): void
    {
        $date = new DateTime('2025-01-01');
        $this->teamBadge->setCreatedAt($date);
        $this->assertSame($date, $this->teamBadge->getCreatedAt());
    }

    public function testUpdatedAtCanBeSet(): void
    {
        $date = new DateTime('2025-06-15');
        $this->teamBadge->setUpdatedAt($date);
        $this->assertSame($date, $this->teamBadge->getUpdatedAt());
    }
}
