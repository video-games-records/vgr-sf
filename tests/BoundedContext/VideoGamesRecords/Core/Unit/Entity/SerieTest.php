<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\SerieBadge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\SerieTranslation;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\SerieStatus;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class SerieTest extends TestCase
{
    private Serie $serie;

    protected function setUp(): void
    {
        $this->serie = new Serie();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyCollections(): void
    {
        $serie = new Serie();
        $this->assertInstanceOf(Collection::class, $serie->getGames());
        $this->assertCount(0, $serie->getGames());

        $this->assertInstanceOf(Collection::class, $serie->getTranslations());
        $this->assertCount(0, $serie->getTranslations());
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->serie->getId());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->serie->setId(2);
        $this->assertSame(2, $this->serie->getId());
        $this->assertSame($this->serie, $result);
    }

    public function testSetAndGetLibSerie(): void
    {
        $result = $this->serie->setLibSerie('Mario');
        $this->assertSame('Mario', $this->serie->getLibSerie());
        $this->assertSame($this->serie, $result);
    }

    public function testStatusDefaultsToInactive(): void
    {
        $this->assertSame(SerieStatus::INACTIVE, $this->serie->getStatus());
    }

    public function testSetAndGetStatus(): void
    {
        $result = $this->serie->setStatus(SerieStatus::ACTIVE);
        $this->assertSame(SerieStatus::ACTIVE, $this->serie->getStatus());
        $this->assertSame($this->serie, $result);
    }

    public function testGetSerieStatus(): void
    {
        $this->serie->setStatus(SerieStatus::ACTIVE);
        $serieStatus = $this->serie->getSerieStatus();
        $this->assertInstanceOf(SerieStatus::class, $serieStatus);
        $this->assertSame(SerieStatus::ACTIVE, $serieStatus->getValue());
    }

    // ------------------------------------------------------------------
    // Trait defaults
    // ------------------------------------------------------------------

    public function testNbChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->serie->getNbChart());
    }

    public function testNbGameDefaultsToZero(): void
    {
        $this->assertSame(0, $this->serie->getNbGame());
    }

    public function testNbPlayerDefaultsToZero(): void
    {
        $this->assertSame(0, $this->serie->getNbPlayer());
    }

    public function testNbTeamDefaultsToZero(): void
    {
        $this->assertSame(0, $this->serie->getNbTeam());
    }

    // ------------------------------------------------------------------
    // Badge relation
    // ------------------------------------------------------------------

    public function testBadgeDefaultsToNull(): void
    {
        $this->assertNull($this->serie->getBadge());
    }

    public function testSetAndGetBadge(): void
    {
        $badge = $this->createMock(SerieBadge::class);
        $result = $this->serie->setBadge($badge);
        $this->assertSame($badge, $this->serie->getBadge());
        $this->assertSame($this->serie, $result);
    }

    public function testSetBadgeToNull(): void
    {
        $badge = $this->createMock(SerieBadge::class);
        $this->serie->setBadge($badge);
        $this->serie->setBadge(null);
        $this->assertNull($this->serie->getBadge());
    }

    // ------------------------------------------------------------------
    // Translation management
    // ------------------------------------------------------------------

    public function testAddTranslation(): void
    {
        $translation = new SerieTranslation();
        $translation->setLocale('en');

        $this->serie->addTranslation($translation);

        $this->assertCount(1, $this->serie->getTranslations());
        $this->assertSame($this->serie, $translation->getTranslatable());
    }

    public function testAddTranslationDoesNotDuplicate(): void
    {
        $translation = new SerieTranslation();
        $translation->setLocale('en');

        $this->serie->addTranslation($translation);
        $this->serie->addTranslation($translation);

        $this->assertCount(1, $this->serie->getTranslations());
    }

    public function testAddTranslationIndexedByLocale(): void
    {
        $translation = new SerieTranslation();
        $translation->setLocale('fr');

        $this->serie->addTranslation($translation);

        $this->assertTrue($this->serie->getTranslations()->containsKey('fr'));
    }

    public function testRemoveTranslation(): void
    {
        $translation = new SerieTranslation();
        $translation->setLocale('en');

        $this->serie->addTranslation($translation);
        $this->serie->removeTranslation($translation);

        $this->assertCount(0, $this->serie->getTranslations());
    }

    public function testGetAvailableLocales(): void
    {
        $en = new SerieTranslation();
        $en->setLocale('en');

        $fr = new SerieTranslation();
        $fr->setLocale('fr');

        $this->serie->addTranslation($en);
        $this->serie->addTranslation($fr);

        $locales = $this->serie->getAvailableLocales();
        $this->assertContains('en', $locales);
        $this->assertContains('fr', $locales);
    }

    // ------------------------------------------------------------------
    // translate() method
    // ------------------------------------------------------------------

    public function testTranslateReturnsTranslationForRequestedLocale(): void
    {
        $en = new SerieTranslation();
        $en->setLocale('en');
        $this->serie->addTranslation($en);

        $result = $this->serie->translate('en');
        $this->assertSame($en, $result);
    }

    public function testTranslateFallsBackToEnglish(): void
    {
        $en = new SerieTranslation();
        $en->setLocale('en');
        $this->serie->addTranslation($en);

        $result = $this->serie->translate('de');
        $this->assertSame($en, $result);
    }

    public function testTranslateReturnsNullWhenEmptyAndNoFallback(): void
    {
        $result = $this->serie->translate('es', false);
        $this->assertNull($result);
    }

    // ------------------------------------------------------------------
    // setDescription / getDescription
    // ------------------------------------------------------------------

    public function testSetDescriptionCreatesTranslation(): void
    {
        $this->serie->setDescription('Mario series', 'en');
        $this->assertSame('Mario series', $this->serie->getDescription('en'));
    }

    public function testSetDescriptionUpdatesExistingTranslation(): void
    {
        $this->serie->setDescription('Mario series', 'en');
        $this->serie->setDescription('Nintendo Mario series', 'en');
        $this->assertSame('Nintendo Mario series', $this->serie->getDescription('en'));
    }

    public function testGetDescriptionReturnsNullWhenNoTranslation(): void
    {
        $this->assertNull($this->serie->getDescription('en'));
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testGetDefaultName(): void
    {
        $this->serie->setLibSerie('Zelda');
        $this->assertSame('Zelda', $this->serie->getDefaultName());
    }

    public function testGetName(): void
    {
        $this->serie->setLibSerie('Zelda');
        $this->assertSame('Zelda', $this->serie->getName());
    }

    public function testToString(): void
    {
        $this->serie->setId(4);
        $this->serie->setLibSerie('Zelda');
        $this->assertSame('Zelda [4]', (string) $this->serie);
    }
}
