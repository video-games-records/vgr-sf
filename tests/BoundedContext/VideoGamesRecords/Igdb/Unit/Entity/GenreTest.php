<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Igdb\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Genre;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class GenreTest extends TestCase
{
    private Genre $genre;

    protected function setUp(): void
    {
        $this->genre = new Genre();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesTimestamps(): void
    {
        $before = new DateTimeImmutable();
        $genre = new Genre();
        $after = new DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $genre->getCreatedAt());
        $this->assertLessThanOrEqual($after, $genre->getCreatedAt());
        $this->assertGreaterThanOrEqual($before, $genre->getUpdatedAt());
        $this->assertLessThanOrEqual($after, $genre->getUpdatedAt());
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $result = $this->genre->setId(7);
        $this->assertSame(7, $this->genre->getId());
        $this->assertSame($this->genre, $result);
    }

    public function testSetAndGetName(): void
    {
        $result = $this->genre->setName('Role-playing (RPG)');
        $this->assertSame('Role-playing (RPG)', $this->genre->getName());
        $this->assertSame($this->genre, $result);
    }

    public function testSetAndGetSlug(): void
    {
        $result = $this->genre->setSlug('role-playing-rpg');
        $this->assertSame('role-playing-rpg', $this->genre->getSlug());
        $this->assertSame($this->genre, $result);
    }

    public function testUrlDefaultsToNull(): void
    {
        $this->assertNull($this->genre->getUrl());
    }

    public function testSetAndGetUrl(): void
    {
        $result = $this->genre->setUrl('https://www.igdb.com/genres/role-playing-rpg');
        $this->assertSame('https://www.igdb.com/genres/role-playing-rpg', $this->genre->getUrl());
        $this->assertSame($this->genre, $result);
    }

    public function testSetUrlToNull(): void
    {
        $this->genre->setUrl('https://www.igdb.com/genres/rpg');
        $this->genre->setUrl(null);
        $this->assertNull($this->genre->getUrl());
    }

    public function testSetAndGetChecksum(): void
    {
        $result = $this->genre->setChecksum('abc-123-def-456-ghi-789');
        $this->assertSame('abc-123-def-456-ghi-789', $this->genre->getChecksum());
        $this->assertSame($this->genre, $result);
    }

    // ------------------------------------------------------------------
    // Timestamps
    // ------------------------------------------------------------------

    public function testSetAndGetCreatedAt(): void
    {
        $date = new DateTimeImmutable('2024-01-15 10:00:00');
        $result = $this->genre->setCreatedAt($date);
        $this->assertSame($date, $this->genre->getCreatedAt());
        $this->assertSame($this->genre, $result);
    }

    public function testSetAndGetUpdatedAt(): void
    {
        $date = new DateTimeImmutable('2024-06-01 12:00:00');
        $result = $this->genre->setUpdatedAt($date);
        $this->assertSame($date, $this->genre->getUpdatedAt());
        $this->assertSame($this->genre, $result);
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->genre->setName('Adventure');
        $this->assertSame('Adventure', (string) $this->genre);
    }
}
