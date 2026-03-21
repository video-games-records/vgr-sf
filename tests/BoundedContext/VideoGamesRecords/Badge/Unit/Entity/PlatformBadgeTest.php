<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlatformBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use PHPUnit\Framework\TestCase;

class PlatformBadgeTest extends TestCase
{
    private PlatformBadge $badge;

    protected function setUp(): void
    {
        $this->badge = new PlatformBadge();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorSetsTypePlatform(): void
    {
        $this->assertSame(BadgeType::PLATFORM, $this->badge->getType());
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
    // platform relation
    // ------------------------------------------------------------------

    public function testPlatformDefaultsToNull(): void
    {
        $this->assertNull($this->badge->getPlatform());
    }

    // ------------------------------------------------------------------
    // isTypeMaster
    // ------------------------------------------------------------------

    public function testIsTypeMasterReturnsFalse(): void
    {
        $this->assertFalse($this->badge->isTypeMaster());
    }

    // ------------------------------------------------------------------
    // majValue (inherited no-op)
    // ------------------------------------------------------------------

    public function testMajValueDoesNotChangeValue(): void
    {
        $this->badge->setValue(75);
        $this->badge->majValue();
        $this->assertSame(75, $this->badge->getValue());
    }

    // ------------------------------------------------------------------
    // getDirectory for PLATFORM type
    // ------------------------------------------------------------------

    public function testPlatformTypeDirectoryIsCorrect(): void
    {
        $this->assertSame('badge' . DIRECTORY_SEPARATOR . 'Platform', $this->badge->getType()->getDirectory());
    }

    // ------------------------------------------------------------------
    // Inherited setters / fluent interface
    // ------------------------------------------------------------------

    public function testSetIdReturnsSelf(): void
    {
        $result = $this->badge->setId(3);
        $this->assertSame($this->badge, $result);
        $this->assertSame(3, $this->badge->getId());
    }

    public function testSetPictureReturnsSelf(): void
    {
        $result = $this->badge->setPicture('platform.gif');
        $this->assertSame($this->badge, $result);
        $this->assertSame('platform.gif', $this->badge->getPicture());
    }

    public function testSetNbPlayerReturnsSelf(): void
    {
        $result = $this->badge->setNbPlayer(12);
        $this->assertSame($this->badge, $result);
        $this->assertSame(12, $this->badge->getNbPlayer());
    }

    public function testSetValueReturnsSelf(): void
    {
        $result = $this->badge->setValue(300);
        $this->assertSame($this->badge, $result);
        $this->assertSame(300, $this->badge->getValue());
    }
}
