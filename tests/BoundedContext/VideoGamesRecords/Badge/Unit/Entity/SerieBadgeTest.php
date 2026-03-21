<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\SerieBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use PHPUnit\Framework\TestCase;

class SerieBadgeTest extends TestCase
{
    private SerieBadge $badge;

    protected function setUp(): void
    {
        $this->badge = new SerieBadge();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorSetsTypeSerie(): void
    {
        $this->assertSame(BadgeType::SERIE, $this->badge->getType());
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
    // serie relation
    // ------------------------------------------------------------------

    public function testSerieDefaultsToNull(): void
    {
        $this->assertNull($this->badge->getSerie());
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
        $this->badge->setValue(99);
        $this->badge->majValue();
        $this->assertSame(99, $this->badge->getValue());
    }

    // ------------------------------------------------------------------
    // getDirectory for SERIE type
    // ------------------------------------------------------------------

    public function testSerieTypeDirectoryIsCorrect(): void
    {
        $this->assertSame('series/badge', $this->badge->getType()->getDirectory());
    }

    // ------------------------------------------------------------------
    // Inherited setters / fluent interface
    // ------------------------------------------------------------------

    public function testSetIdReturnsSelf(): void
    {
        $result = $this->badge->setId(7);
        $this->assertSame($this->badge, $result);
        $this->assertSame(7, $this->badge->getId());
    }

    public function testSetPictureReturnsSelf(): void
    {
        $result = $this->badge->setPicture('serie.gif');
        $this->assertSame($this->badge, $result);
        $this->assertSame('serie.gif', $this->badge->getPicture());
    }

    public function testSetNbPlayerReturnsSelf(): void
    {
        $result = $this->badge->setNbPlayer(5);
        $this->assertSame($this->badge, $result);
        $this->assertSame(5, $this->badge->getNbPlayer());
    }
}
