<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\SerieTranslation;
use PHPUnit\Framework\TestCase;

class SerieTranslationTest extends TestCase
{
    private SerieTranslation $translation;

    protected function setUp(): void
    {
        $this->translation = new SerieTranslation();
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->translation->getId());
    }

    public function testDescriptionDefaultsToNull(): void
    {
        $this->assertNull($this->translation->getDescription());
    }

    public function testSetAndGetDescription(): void
    {
        $result = $this->translation->setDescription('A classic Nintendo franchise');
        $this->assertSame('A classic Nintendo franchise', $this->translation->getDescription());
        $this->assertSame($this->translation, $result);
    }

    public function testSetDescriptionToNull(): void
    {
        $this->translation->setDescription('Some description');
        $this->translation->setDescription(null);
        $this->assertNull($this->translation->getDescription());
    }

    public function testSetAndGetLocale(): void
    {
        $result = $this->translation->setLocale('en');
        $this->assertSame('en', $this->translation->getLocale());
        $this->assertSame($this->translation, $result);
    }

    // ------------------------------------------------------------------
    // Translatable (Serie) relation
    // ------------------------------------------------------------------

    public function testSetAndGetTranslatable(): void
    {
        $serie = $this->createMock(Serie::class);
        $result = $this->translation->setTranslatable($serie);
        $this->assertSame($serie, $this->translation->getTranslatable());
        $this->assertSame($this->translation, $result);
    }
}
