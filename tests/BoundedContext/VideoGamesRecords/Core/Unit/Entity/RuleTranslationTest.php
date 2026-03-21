<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Rule;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\RuleTranslation;
use PHPUnit\Framework\TestCase;

class RuleTranslationTest extends TestCase
{
    private RuleTranslation $translation;

    protected function setUp(): void
    {
        $this->translation = new RuleTranslation();
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->translation->getId());
    }

    public function testContentDefaultsToEmptyString(): void
    {
        $this->assertSame('', $this->translation->getContent());
    }

    public function testSetAndGetContent(): void
    {
        $result = $this->translation->setContent('No cheating allowed');
        $this->assertSame('No cheating allowed', $this->translation->getContent());
        $this->assertSame($this->translation, $result);
    }

    public function testSetAndGetLocale(): void
    {
        $result = $this->translation->setLocale('en');
        $this->assertSame('en', $this->translation->getLocale());
        $this->assertSame($this->translation, $result);
    }

    // ------------------------------------------------------------------
    // Translatable (Rule) relation
    // ------------------------------------------------------------------

    public function testSetAndGetTranslatable(): void
    {
        $rule = $this->createMock(Rule::class);
        $result = $this->translation->setTranslatable($rule);
        $this->assertSame($rule, $this->translation->getTranslatable());
        $this->assertSame($this->translation, $result);
    }
}
