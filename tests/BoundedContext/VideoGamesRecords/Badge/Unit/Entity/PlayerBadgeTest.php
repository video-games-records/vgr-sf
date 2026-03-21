<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use DateTime;
use PHPUnit\Framework\TestCase;

class PlayerBadgeTest extends TestCase
{
    private PlayerBadge $playerBadge;

    protected function setUp(): void
    {
        $this->playerBadge = new PlayerBadge();
    }

    // ------------------------------------------------------------------
    // Default values
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->playerBadge->getId());
    }

    public function testEndedAtDefaultsToNull(): void
    {
        $this->assertNull($this->playerBadge->getEndedAt());
    }

    public function testMbOrderDefaultsToNull(): void
    {
        $this->assertNull($this->playerBadge->getMbOrder());
    }

    // ------------------------------------------------------------------
    // id
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $result = $this->playerBadge->setId(1);
        $this->assertSame(1, $this->playerBadge->getId());
        $this->assertSame($this->playerBadge, $result);
    }

    // ------------------------------------------------------------------
    // endedAt
    // ------------------------------------------------------------------

    public function testSetAndGetEndedAt(): void
    {
        $date = new DateTime('2025-12-31');
        $result = $this->playerBadge->setEndedAt($date);
        $this->assertSame($date, $this->playerBadge->getEndedAt());
        $this->assertSame($this->playerBadge, $result);
    }

    // ------------------------------------------------------------------
    // mbOrder
    // ------------------------------------------------------------------

    public function testSetAndGetMbOrder(): void
    {
        $result = $this->playerBadge->setMbOrder(3);
        $this->assertSame(3, $this->playerBadge->getMbOrder());
        $this->assertSame($this->playerBadge, $result);
    }

    // ------------------------------------------------------------------
    // badge relation
    // ------------------------------------------------------------------

    public function testSetAndGetBadge(): void
    {
        $badge = $this->createMock(Badge::class);
        $result = $this->playerBadge->setBadge($badge);
        $this->assertSame($badge, $this->playerBadge->getBadge());
        $this->assertSame($this->playerBadge, $result);
    }

    // ------------------------------------------------------------------
    // player relation
    // ------------------------------------------------------------------

    public function testSetAndGetPlayer(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->playerBadge->setPlayer($player);
        $this->assertSame($player, $this->playerBadge->getPlayer());
        $this->assertSame($this->playerBadge, $result);
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getPseudo')->willReturn('Alice');

        $badge = $this->createMock(Badge::class);
        $badge->method('__toString')->willReturn('Master / master.gif [10]');

        $this->playerBadge->setPlayer($player);
        $this->playerBadge->setBadge($badge);

        $this->assertSame('Alice # Master / master.gif [10] ', (string) $this->playerBadge);
    }

    // ------------------------------------------------------------------
    // TimestampableEntity (trait presence check)
    // ------------------------------------------------------------------

    public function testCreatedAtCanBeSet(): void
    {
        $date = new DateTime('2025-01-01');
        $this->playerBadge->setCreatedAt($date);
        $this->assertSame($date, $this->playerBadge->getCreatedAt());
    }

    public function testUpdatedAtCanBeSet(): void
    {
        $date = new DateTime('2025-06-15');
        $this->playerBadge->setUpdatedAt($date);
        $this->assertSame($date, $this->playerBadge->getUpdatedAt());
    }
}
