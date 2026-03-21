<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Rule;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\RuleTranslation;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    private Rule $rule;

    protected function setUp(): void
    {
        $this->rule = new Rule();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyCollections(): void
    {
        $rule = new Rule();
        $this->assertInstanceOf(Collection::class, $rule->getTranslations());
        $this->assertCount(0, $rule->getTranslations());

        $this->assertInstanceOf(Collection::class, $rule->getGames());
        $this->assertCount(0, $rule->getGames());
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->rule->getId());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->rule->setId(3);
        $this->assertSame(3, $this->rule->getId());
        $this->assertSame($this->rule, $result);
    }

    public function testSetAndGetName(): void
    {
        $result = $this->rule->setName('No cheating');
        $this->assertSame('No cheating', $this->rule->getName());
        $this->assertSame($this->rule, $result);
    }

    // ------------------------------------------------------------------
    // Player relation
    // ------------------------------------------------------------------

    public function testPlayerDefaultsToNull(): void
    {
        $this->assertNull($this->rule->getPlayer());
    }

    public function testSetAndGetPlayer(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->rule->setPlayer($player);
        $this->assertSame($player, $this->rule->getPlayer());
        $this->assertSame($this->rule, $result);
    }

    // ------------------------------------------------------------------
    // Translation management
    // ------------------------------------------------------------------

    public function testAddTranslation(): void
    {
        $translation = new RuleTranslation();
        $translation->setLocale('en');
        $translation->setContent('No cheating allowed');

        $this->rule->addTranslation($translation);

        $this->assertCount(1, $this->rule->getTranslations());
        $this->assertSame($this->rule, $translation->getTranslatable());
    }

    public function testAddTranslationDoesNotDuplicate(): void
    {
        $translation = new RuleTranslation();
        $translation->setLocale('en');
        $translation->setContent('No cheating allowed');

        $this->rule->addTranslation($translation);
        $this->rule->addTranslation($translation);

        $this->assertCount(1, $this->rule->getTranslations());
    }

    public function testAddTranslationIndexedByLocale(): void
    {
        $translation = new RuleTranslation();
        $translation->setLocale('fr');
        $translation->setContent('Pas de triche');

        $this->rule->addTranslation($translation);

        $this->assertTrue($this->rule->getTranslations()->containsKey('fr'));
    }

    public function testRemoveTranslation(): void
    {
        $translation = new RuleTranslation();
        $translation->setLocale('en');
        $translation->setContent('No cheating allowed');

        $this->rule->addTranslation($translation);
        $this->rule->removeTranslation($translation);

        $this->assertCount(0, $this->rule->getTranslations());
    }

    public function testGetAvailableLocales(): void
    {
        $en = new RuleTranslation();
        $en->setLocale('en');
        $en->setContent('No cheating');

        $fr = new RuleTranslation();
        $fr->setLocale('fr');
        $fr->setContent('Pas de triche');

        $this->rule->addTranslation($en);
        $this->rule->addTranslation($fr);

        $locales = $this->rule->getAvailableLocales();
        $this->assertContains('en', $locales);
        $this->assertContains('fr', $locales);
    }

    // ------------------------------------------------------------------
    // translate() method
    // ------------------------------------------------------------------

    public function testTranslateReturnsTranslationForRequestedLocale(): void
    {
        $en = new RuleTranslation();
        $en->setLocale('en');
        $en->setContent('No cheating');
        $this->rule->addTranslation($en);

        $result = $this->rule->translate('en');
        $this->assertSame($en, $result);
    }

    public function testTranslateFallsBackToDefaultLocale(): void
    {
        $en = new RuleTranslation();
        $en->setLocale('en');
        $en->setContent('No cheating');
        $this->rule->addTranslation($en);

        $result = $this->rule->translate('de');
        $this->assertSame($en, $result);
    }

    public function testTranslateReturnsNullWhenEmptyAndNoFallback(): void
    {
        $result = $this->rule->translate('es', false);
        $this->assertNull($result);
    }

    // ------------------------------------------------------------------
    // setContent / getContent
    // ------------------------------------------------------------------

    public function testSetContentCreatesTranslation(): void
    {
        $this->rule->setContent('No cheating', 'en');
        $this->assertSame('No cheating', $this->rule->getContent('en'));
    }

    public function testSetContentUpdatesExistingTranslation(): void
    {
        $this->rule->setContent('No cheating', 'en');
        $this->rule->setContent('Absolutely no cheating', 'en');
        $this->assertSame('Absolutely no cheating', $this->rule->getContent('en'));
    }

    public function testGetContentReturnsNullWhenNoTranslation(): void
    {
        $this->assertNull($this->rule->getContent('en'));
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->rule->setId(1);
        $this->rule->setName('No cheating');
        $this->assertSame('No cheating [1]', (string) $this->rule);
    }
}
