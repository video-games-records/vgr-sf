<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Igdb\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\PlatformType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PlatformTypeTest extends TestCase
{
    private PlatformType $platformType;

    protected function setUp(): void
    {
        $this->platformType = new PlatformType();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesTimestamps(): void
    {
        $before = new DateTimeImmutable();
        $platformType = new PlatformType();
        $after = new DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $platformType->getCreatedAt());
        $this->assertLessThanOrEqual($after, $platformType->getCreatedAt());
        $this->assertGreaterThanOrEqual($before, $platformType->getUpdatedAt());
        $this->assertLessThanOrEqual($after, $platformType->getUpdatedAt());
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $result = $this->platformType->setId(3);
        $this->assertSame(3, $this->platformType->getId());
        $this->assertSame($this->platformType, $result);
    }

    public function testSetAndGetName(): void
    {
        $result = $this->platformType->setName('Console');
        $this->assertSame('Console', $this->platformType->getName());
        $this->assertSame($this->platformType, $result);
    }

    public function testChecksumDefaultsToNull(): void
    {
        $this->assertNull($this->platformType->getChecksum());
    }

    public function testSetAndGetChecksum(): void
    {
        $result = $this->platformType->setChecksum('abc-123-def');
        $this->assertSame('abc-123-def', $this->platformType->getChecksum());
        $this->assertSame($this->platformType, $result);
    }

    public function testSetChecksumToNull(): void
    {
        $this->platformType->setChecksum('abc-123-def');
        $this->platformType->setChecksum(null);
        $this->assertNull($this->platformType->getChecksum());
    }

    // ------------------------------------------------------------------
    // Timestamps
    // ------------------------------------------------------------------

    public function testSetAndGetCreatedAt(): void
    {
        $date = new DateTimeImmutable('2024-01-15 10:00:00');
        $result = $this->platformType->setCreatedAt($date);
        $this->assertSame($date, $this->platformType->getCreatedAt());
        $this->assertSame($this->platformType, $result);
    }

    public function testSetAndGetUpdatedAt(): void
    {
        $date = new DateTimeImmutable('2024-06-01 12:00:00');
        $result = $this->platformType->setUpdatedAt($date);
        $this->assertSame($date, $this->platformType->getUpdatedAt());
        $this->assertSame($this->platformType, $result);
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->platformType->setName('Arcade');
        $this->assertSame('Arcade', (string) $this->platformType);
    }
}
