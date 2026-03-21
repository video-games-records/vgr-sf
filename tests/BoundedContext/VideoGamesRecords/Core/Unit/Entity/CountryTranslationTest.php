<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\CountryTranslation;
use PHPUnit\Framework\TestCase;

class CountryTranslationTest extends TestCase
{
    private CountryTranslation $translation;

    protected function setUp(): void
    {
        $this->translation = new CountryTranslation();
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->translation->getId());
    }

    public function testNameDefaultsToEmptyString(): void
    {
        $this->assertSame('', $this->translation->getName());
    }

    public function testSetAndGetName(): void
    {
        $result = $this->translation->setName('France');
        $this->assertSame('France', $this->translation->getName());
        $this->assertSame($this->translation, $result);
    }

    public function testSetAndGetLocale(): void
    {
        $result = $this->translation->setLocale('fr');
        $this->assertSame('fr', $this->translation->getLocale());
        $this->assertSame($this->translation, $result);
    }

    // ------------------------------------------------------------------
    // Translatable (Country) relation
    // ------------------------------------------------------------------

    public function testSetAndGetTranslatable(): void
    {
        $country = $this->createMock(Country::class);
        $result = $this->translation->setTranslatable($country);
        $this->assertSame($country, $this->translation->getTranslatable());
        $this->assertSame($this->translation, $result);
    }
}
