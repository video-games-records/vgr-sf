<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\CountryBadge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\CountryTranslation;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class CountryTest extends TestCase
{
    private Country $country;

    protected function setUp(): void
    {
        $this->country = new Country();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyTranslations(): void
    {
        $country = new Country();
        $this->assertInstanceOf(Collection::class, $country->getTranslations());
        $this->assertCount(0, $country->getTranslations());
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->country->getId());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->country->setId(1);
        $this->assertSame(1, $this->country->getId());
        $this->assertSame($this->country, $result);
    }

    public function testSetAndGetCodeIso2(): void
    {
        $result = $this->country->setCodeIso2('FR');
        $this->assertSame('FR', $this->country->getCodeIso2());
        $this->assertSame($this->country, $result);
    }

    public function testSetAndGetCodeIso3(): void
    {
        $result = $this->country->setCodeIso3('FRA');
        $this->assertSame('FRA', $this->country->getCodeIso3());
        $this->assertSame($this->country, $result);
    }

    public function testSetAndGetCodeIsoNumeric(): void
    {
        $result = $this->country->setCodeIsoNumeric(250);
        $this->assertSame(250, $this->country->getCodeIsoNumeric());
        $this->assertSame($this->country, $result);
    }

    public function testSetAndGetSlug(): void
    {
        $result = $this->country->setSlug('france');
        $this->assertSame('france', $this->country->getSlug());
        $this->assertSame($this->country, $result);
    }

    // ------------------------------------------------------------------
    // Badge relation
    // ------------------------------------------------------------------

    public function testBadgeDefaultsToNull(): void
    {
        $this->assertNull($this->country->getBadge());
    }

    public function testSetAndGetBadge(): void
    {
        $badge = $this->createMock(CountryBadge::class);
        $result = $this->country->setBadge($badge);
        $this->assertSame($badge, $this->country->getBadge());
        $this->assertSame($this->country, $result);
    }

    public function testSetBadgeToNull(): void
    {
        $badge = $this->createMock(CountryBadge::class);
        $this->country->setBadge($badge);
        $this->country->setBadge(null);
        $this->assertNull($this->country->getBadge());
    }

    // ------------------------------------------------------------------
    // Translation management
    // ------------------------------------------------------------------

    public function testAddTranslation(): void
    {
        $translation = new CountryTranslation();
        $translation->setLocale('en');
        $translation->setName('France');

        $this->country->addTranslation($translation);

        $this->assertCount(1, $this->country->getTranslations());
        $this->assertSame($this->country, $translation->getTranslatable());
    }

    public function testAddTranslationDoesNotDuplicate(): void
    {
        $translation = new CountryTranslation();
        $translation->setLocale('en');
        $translation->setName('France');

        $this->country->addTranslation($translation);
        $this->country->addTranslation($translation);

        $this->assertCount(1, $this->country->getTranslations());
    }

    public function testAddTranslationIndexedByLocale(): void
    {
        $translation = new CountryTranslation();
        $translation->setLocale('fr');
        $translation->setName('France');

        $this->country->addTranslation($translation);

        $this->assertTrue($this->country->getTranslations()->containsKey('fr'));
    }

    public function testRemoveTranslation(): void
    {
        $translation = new CountryTranslation();
        $translation->setLocale('en');
        $translation->setName('France');

        $this->country->addTranslation($translation);
        $this->country->removeTranslation($translation);

        $this->assertCount(0, $this->country->getTranslations());
    }

    public function testGetAvailableLocales(): void
    {
        $en = new CountryTranslation();
        $en->setLocale('en');
        $en->setName('France');

        $fr = new CountryTranslation();
        $fr->setLocale('fr');
        $fr->setName('France');

        $this->country->addTranslation($en);
        $this->country->addTranslation($fr);

        $locales = $this->country->getAvailableLocales();
        $this->assertContains('en', $locales);
        $this->assertContains('fr', $locales);
    }

    // ------------------------------------------------------------------
    // translate() method
    // ------------------------------------------------------------------

    public function testTranslateReturnsTranslationForRequestedLocale(): void
    {
        $en = new CountryTranslation();
        $en->setLocale('en');
        $en->setName('France');
        $this->country->addTranslation($en);

        $result = $this->country->translate('en');
        $this->assertSame($en, $result);
    }

    public function testTranslateFallsBackToDefaultLocale(): void
    {
        $en = new CountryTranslation();
        $en->setLocale('en');
        $en->setName('France');
        $this->country->addTranslation($en);

        $result = $this->country->translate('de');
        $this->assertSame($en, $result);
    }

    public function testTranslateReturnsNullWhenNoFallbackAndLocaleNotFound(): void
    {
        $result = $this->country->translate('es', false);
        $this->assertNull($result);
    }

    // ------------------------------------------------------------------
    // setName / getName
    // ------------------------------------------------------------------

    public function testSetNameCreatesTranslation(): void
    {
        $this->country->setName('France', 'en');
        $this->assertSame('France', $this->country->getName('en'));
    }

    public function testSetNameUpdatesExistingTranslation(): void
    {
        $this->country->setName('France', 'en');
        $this->country->setName('United Kingdom', 'en');
        $this->assertSame('United Kingdom', $this->country->getName('en'));
    }

    // ------------------------------------------------------------------
    // getDefaultName
    // ------------------------------------------------------------------

    public function testGetDefaultNameReturnsEnglishTranslation(): void
    {
        $en = new CountryTranslation();
        $en->setLocale('en');
        $en->setName('Germany');
        $this->country->addTranslation($en);

        $this->assertSame('Germany', $this->country->getDefaultName());
    }

    public function testGetDefaultNameReturnsEmptyStringWhenNoEnglishTranslation(): void
    {
        $this->assertSame('', $this->country->getDefaultName());
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->country->setId(5);
        $en = new CountryTranslation();
        $en->setLocale('en');
        $en->setName('Italy');
        $this->country->addTranslation($en);

        $this->assertSame('Italy [5]', (string) $this->country);
    }
}
