<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\MasterBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use PHPUnit\Framework\TestCase;

class MasterBadgeTest extends TestCase
{
    private MasterBadge $badge;

    protected function setUp(): void
    {
        $this->badge = new MasterBadge();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorSetsTypeMaster(): void
    {
        $this->assertSame(BadgeType::MASTER, $this->badge->getType());
    }

    // ------------------------------------------------------------------
    // Inherited defaults
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->badge->getId());
    }

    public function testValueDefaultsToZero(): void
    {
        $this->assertSame(0, $this->badge->getValue());
    }

    public function testNbPlayerDefaultsToZero(): void
    {
        $this->assertSame(0, $this->badge->getNbPlayer());
    }

    // ------------------------------------------------------------------
    // game relation
    // ------------------------------------------------------------------

    public function testGameDefaultsToNull(): void
    {
        $this->assertNull($this->badge->getGame());
    }

    // ------------------------------------------------------------------
    // isTypeMaster
    // ------------------------------------------------------------------

    public function testIsTypeMasterReturnsTrue(): void
    {
        $this->assertTrue($this->badge->isTypeMaster());
    }

    // ------------------------------------------------------------------
    // majValue – game is null
    // ------------------------------------------------------------------

    public function testMajValueSetsZeroWhenNoGame(): void
    {
        $this->badge->setValue(500);
        $this->badge->majValue();
        $this->assertSame(0, $this->badge->getValue());
    }

    public function testMajValueSetsZeroWhenNbPlayerIsZero(): void
    {
        $game = $this->createMock(Game::class);
        $game->method('getNbPlayer')->willReturn(1000);

        $this->badge->setNbPlayer(0);
        $this->badge->majValue($game);

        $this->assertSame(0, $this->badge->getValue());
    }

    // ------------------------------------------------------------------
    // majValue – formula correctness
    // ------------------------------------------------------------------

    public function testMajValueComputesExpectedValue(): void
    {
        $game = $this->createMock(Game::class);
        $game->method('getNbPlayer')->willReturn(1000);

        $this->badge->setNbPlayer(100);
        $this->badge->majValue($game);

        // Expected: nbPlayerDiff = 100 + 1000 - 100 = 1000
        // factor = 6250 * (-1/1000 + 0.0102) = 6250 * 0.009200 = 57.5
        // divisor = 100^(1/3) ≈ 4.6416
        // value = floor(100 * 57.5 / 4.6416) ≈ floor(1238.8) = 1238
        $expected = (int) floor(100 * (6250 * (-1 / 1000 + 0.0102)) / pow(100, 1 / 3));

        $this->assertSame($expected, $this->badge->getValue());
    }

    public function testMajValueWithPassedGameArgumentOverridesRelation(): void
    {
        $game = $this->createMock(Game::class);
        $game->method('getNbPlayer')->willReturn(500);

        $this->badge->setNbPlayer(50);
        $this->badge->majValue($game);

        $expected = (int) floor(100 * (6250 * (-1 / (100 + 500 - 50) + 0.0102)) / pow(50, 1 / 3));

        $this->assertSame($expected, $this->badge->getValue());
    }

    // ------------------------------------------------------------------
    // Setters return value (fluent interface – inherited)
    // ------------------------------------------------------------------

    public function testSetValueReturnsSelf(): void
    {
        $result = $this->badge->setValue(100);
        $this->assertSame($this->badge, $result);
    }

    public function testSetNbPlayerReturnsSelf(): void
    {
        $result = $this->badge->setNbPlayer(20);
        $this->assertSame($this->badge, $result);
    }
}
