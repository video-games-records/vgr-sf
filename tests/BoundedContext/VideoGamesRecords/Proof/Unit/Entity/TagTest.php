<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Proof\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Tag;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    private Tag $tag;

    protected function setUp(): void
    {
        $this->tag = new Tag();
    }

    // ------------------------------------------------------------------
    // Basic properties — defaults
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->tag->getId());
    }

    public function testCategoryDefaultsToNull(): void
    {
        $this->assertNull($this->tag->getCategory());
    }

    public function testIsOfficialDefaultsToFalse(): void
    {
        $this->assertFalse($this->tag->isOfficial());
        $this->assertFalse($this->tag->getIsOfficial());
    }

    // ------------------------------------------------------------------
    // Getters / setters
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $result = $this->tag->setId(7);
        $this->assertSame(7, $this->tag->getId());
        $this->assertSame($this->tag, $result);
    }

    public function testSetAndGetName(): void
    {
        $result = $this->tag->setName('Speedrun');
        $this->assertSame('Speedrun', $this->tag->getName());
        $this->assertSame($this->tag, $result);
    }

    public function testSetAndGetCategory(): void
    {
        $result = $this->tag->setCategory('Genre');
        $this->assertSame('Genre', $this->tag->getCategory());
        $this->assertSame($this->tag, $result);
    }

    public function testSetCategoryToNull(): void
    {
        $this->tag->setCategory('Genre');
        $this->tag->setCategory(null);
        $this->assertNull($this->tag->getCategory());
    }

    public function testSetIsOfficialToTrue(): void
    {
        $result = $this->tag->setIsOfficial(true);
        $this->assertTrue($this->tag->isOfficial());
        $this->assertTrue($this->tag->getIsOfficial());
        $this->assertSame($this->tag, $result);
    }

    public function testSetIsOfficialToFalse(): void
    {
        $this->tag->setIsOfficial(true);
        $this->tag->setIsOfficial(false);
        $this->assertFalse($this->tag->isOfficial());
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->tag->setId(3);
        $this->tag->setName('NoGlitch');
        $this->assertSame('NoGlitch [3]', (string) $this->tag);
    }

    public function testToStringWithNullId(): void
    {
        $this->tag->setName('Any%');
        $this->assertSame('Any% []', (string) $this->tag);
    }
}
