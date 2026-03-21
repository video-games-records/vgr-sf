<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\CountryBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;
use PHPUnit\Framework\TestCase;

class CountryBadgeTest extends TestCase
{
    private CountryBadge $badge;

    protected function setUp(): void
    {
        $this->badge = new CountryBadge();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorSetsTypeVgrSpecialCountry(): void
    {
        $this->assertSame(BadgeType::VGR_SPECIAL_COUNTRY, $this->badge->getType());
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
    // country relation
    // ------------------------------------------------------------------

    public function testCountryDefaultsToNull(): void
    {
        $this->assertNull($this->badge->getCountry());
    }

    // ------------------------------------------------------------------
    // isTypeMaster
    // ------------------------------------------------------------------

    public function testIsTypeMasterReturnsFalse(): void
    {
        $this->assertFalse($this->badge->isTypeMaster());
    }

    // ------------------------------------------------------------------
    // VGR_SPECIAL_COUNTRY is a "special" type
    // ------------------------------------------------------------------

    public function testTypeIsSpecial(): void
    {
        $this->assertTrue($this->badge->getType()->isSpecial());
    }

    // ------------------------------------------------------------------
    // majValue (inherited no-op)
    // ------------------------------------------------------------------

    public function testMajValueDoesNotChangeValue(): void
    {
        $this->badge->setValue(50);
        $this->badge->majValue();
        $this->assertSame(50, $this->badge->getValue());
    }

    // ------------------------------------------------------------------
    // getDirectory for VGR_SPECIAL_COUNTRY type
    // ------------------------------------------------------------------

    public function testCountryTypeDirectoryIsCorrect(): void
    {
        $this->assertSame('badge' . DIRECTORY_SEPARATOR . 'VgrSpecialCountry', $this->badge->getType()->getDirectory());
    }

    // ------------------------------------------------------------------
    // Inherited setters / fluent interface
    // ------------------------------------------------------------------

    public function testSetIdReturnsSelf(): void
    {
        $result = $this->badge->setId(11);
        $this->assertSame($this->badge, $result);
        $this->assertSame(11, $this->badge->getId());
    }

    public function testSetPictureReturnsSelf(): void
    {
        $result = $this->badge->setPicture('country.gif');
        $this->assertSame($this->badge, $result);
        $this->assertSame('country.gif', $this->badge->getPicture());
    }

    public function testSetNbPlayerReturnsSelf(): void
    {
        $result = $this->badge->setNbPlayer(8);
        $this->assertSame($this->badge, $result);
        $this->assertSame(8, $this->badge->getNbPlayer());
    }

    public function testSetValueReturnsSelf(): void
    {
        $result = $this->badge->setValue(200);
        $this->assertSame($this->badge, $result);
        $this->assertSame(200, $this->badge->getValue());
    }
}
