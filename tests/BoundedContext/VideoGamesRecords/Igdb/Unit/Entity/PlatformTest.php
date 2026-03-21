<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Igdb\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Platform;
use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\PlatformLogo;
use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\PlatformType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PlatformTest extends TestCase
{
    private Platform $platform;

    protected function setUp(): void
    {
        $this->platform = new Platform();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesTimestamps(): void
    {
        $before = new DateTimeImmutable();
        $platform = new Platform();
        $after = new DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $platform->getCreatedAt());
        $this->assertLessThanOrEqual($after, $platform->getCreatedAt());
        $this->assertGreaterThanOrEqual($before, $platform->getUpdatedAt());
        $this->assertLessThanOrEqual($after, $platform->getUpdatedAt());
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $result = $this->platform->setId(6);
        $this->assertSame(6, $this->platform->getId());
        $this->assertSame($this->platform, $result);
    }

    public function testSetAndGetName(): void
    {
        $result = $this->platform->setName('PlayStation 5');
        $this->assertSame('PlayStation 5', $this->platform->getName());
        $this->assertSame($this->platform, $result);
    }

    public function testAbbreviationDefaultsToNull(): void
    {
        $this->assertNull($this->platform->getAbbreviation());
    }

    public function testSetAndGetAbbreviation(): void
    {
        $result = $this->platform->setAbbreviation('PS5');
        $this->assertSame('PS5', $this->platform->getAbbreviation());
        $this->assertSame($this->platform, $result);
    }

    public function testSetAbbreviationToNull(): void
    {
        $this->platform->setAbbreviation('PS5');
        $this->platform->setAbbreviation(null);
        $this->assertNull($this->platform->getAbbreviation());
    }

    public function testAlternativeNameDefaultsToNull(): void
    {
        $this->assertNull($this->platform->getAlternativeName());
    }

    public function testSetAndGetAlternativeName(): void
    {
        $result = $this->platform->setAlternativeName('PS5');
        $this->assertSame('PS5', $this->platform->getAlternativeName());
        $this->assertSame($this->platform, $result);
    }

    public function testSetAlternativeNameToNull(): void
    {
        $this->platform->setAlternativeName('PS5');
        $this->platform->setAlternativeName(null);
        $this->assertNull($this->platform->getAlternativeName());
    }

    public function testGenerationDefaultsToNull(): void
    {
        $this->assertNull($this->platform->getGeneration());
    }

    public function testSetAndGetGeneration(): void
    {
        $result = $this->platform->setGeneration(9);
        $this->assertSame(9, $this->platform->getGeneration());
        $this->assertSame($this->platform, $result);
    }

    public function testSetGenerationToNull(): void
    {
        $this->platform->setGeneration(9);
        $this->platform->setGeneration(null);
        $this->assertNull($this->platform->getGeneration());
    }

    public function testSlugDefaultsToNull(): void
    {
        $this->assertNull($this->platform->getSlug());
    }

    public function testSetAndGetSlug(): void
    {
        $result = $this->platform->setSlug('playstation-5');
        $this->assertSame('playstation-5', $this->platform->getSlug());
        $this->assertSame($this->platform, $result);
    }

    public function testSetSlugToNull(): void
    {
        $this->platform->setSlug('playstation-5');
        $this->platform->setSlug(null);
        $this->assertNull($this->platform->getSlug());
    }

    public function testSummaryDefaultsToNull(): void
    {
        $this->assertNull($this->platform->getSummary());
    }

    public function testSetAndGetSummary(): void
    {
        $result = $this->platform->setSummary('Sony\'s ninth generation console.');
        $this->assertSame('Sony\'s ninth generation console.', $this->platform->getSummary());
        $this->assertSame($this->platform, $result);
    }

    public function testSetSummaryToNull(): void
    {
        $this->platform->setSummary('Some summary');
        $this->platform->setSummary(null);
        $this->assertNull($this->platform->getSummary());
    }

    public function testUrlDefaultsToNull(): void
    {
        $this->assertNull($this->platform->getUrl());
    }

    public function testSetAndGetUrl(): void
    {
        $result = $this->platform->setUrl('https://www.igdb.com/platforms/ps5');
        $this->assertSame('https://www.igdb.com/platforms/ps5', $this->platform->getUrl());
        $this->assertSame($this->platform, $result);
    }

    public function testSetUrlToNull(): void
    {
        $this->platform->setUrl('https://www.igdb.com/platforms/ps5');
        $this->platform->setUrl(null);
        $this->assertNull($this->platform->getUrl());
    }

    public function testChecksumDefaultsToNull(): void
    {
        $this->assertNull($this->platform->getChecksum());
    }

    public function testSetAndGetChecksum(): void
    {
        $result = $this->platform->setChecksum('abc-123');
        $this->assertSame('abc-123', $this->platform->getChecksum());
        $this->assertSame($this->platform, $result);
    }

    public function testSetChecksumToNull(): void
    {
        $this->platform->setChecksum('abc-123');
        $this->platform->setChecksum(null);
        $this->assertNull($this->platform->getChecksum());
    }

    // ------------------------------------------------------------------
    // Timestamps
    // ------------------------------------------------------------------

    public function testSetAndGetCreatedAt(): void
    {
        $date = new DateTimeImmutable('2024-01-15 10:00:00');
        $result = $this->platform->setCreatedAt($date);
        $this->assertSame($date, $this->platform->getCreatedAt());
        $this->assertSame($this->platform, $result);
    }

    public function testSetAndGetUpdatedAt(): void
    {
        $date = new DateTimeImmutable('2024-06-01 12:00:00');
        $result = $this->platform->setUpdatedAt($date);
        $this->assertSame($date, $this->platform->getUpdatedAt());
        $this->assertSame($this->platform, $result);
    }

    // ------------------------------------------------------------------
    // PlatformType relation
    // ------------------------------------------------------------------

    public function testPlatformTypeDefaultsToNull(): void
    {
        $this->assertNull($this->platform->getPlatformType());
    }

    public function testSetAndGetPlatformType(): void
    {
        $platformType = $this->createMock(PlatformType::class);
        $result = $this->platform->setPlatformType($platformType);
        $this->assertSame($platformType, $this->platform->getPlatformType());
        $this->assertSame($this->platform, $result);
    }

    public function testSetPlatformTypeToNull(): void
    {
        $platformType = $this->createMock(PlatformType::class);
        $this->platform->setPlatformType($platformType);
        $this->platform->setPlatformType(null);
        $this->assertNull($this->platform->getPlatformType());
    }

    // ------------------------------------------------------------------
    // PlatformLogo relation
    // ------------------------------------------------------------------

    public function testPlatformLogoDefaultsToNull(): void
    {
        $this->assertNull($this->platform->getPlatformLogo());
    }

    public function testSetAndGetPlatformLogo(): void
    {
        $platformLogo = $this->createMock(PlatformLogo::class);
        $result = $this->platform->setPlatformLogo($platformLogo);
        $this->assertSame($platformLogo, $this->platform->getPlatformLogo());
        $this->assertSame($this->platform, $result);
    }

    public function testSetPlatformLogoToNull(): void
    {
        $platformLogo = $this->createMock(PlatformLogo::class);
        $this->platform->setPlatformLogo($platformLogo);
        $this->platform->setPlatformLogo(null);
        $this->assertNull($this->platform->getPlatformLogo());
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToStringWithoutAbbreviation(): void
    {
        $this->platform->setName('PC (Microsoft Windows)');
        $this->assertSame('PC (Microsoft Windows)', (string) $this->platform);
    }

    public function testToStringWithAbbreviation(): void
    {
        $this->platform->setName('PlayStation 5');
        $this->platform->setAbbreviation('PS5');
        $this->assertSame('PlayStation 5 (PS5)', (string) $this->platform);
    }
}
