<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Forum\Unit\Entity;

use App\BoundedContext\Forum\Domain\Entity\Category;
use App\BoundedContext\Forum\Domain\Entity\Forum;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    private Category $category;

    protected function setUp(): void
    {
        $this->category = new Category();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyForumsCollection(): void
    {
        $category = new Category();

        $this->assertInstanceOf(Collection::class, $category->getForums());
        $this->assertCount(0, $category->getForums());
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->category->getId());
    }

    public function testSetAndGetName(): void
    {
        $this->category->setName('General');
        $this->assertSame('General', $this->category->getName());
    }

    public function testSetNameReturnsSelf(): void
    {
        $result = $this->category->setName('General');
        $this->assertSame($this->category, $result);
    }

    public function testPositionDefaultsToZero(): void
    {
        $this->assertSame(0, $this->category->getPosition());
    }

    public function testSetAndGetPosition(): void
    {
        $this->category->setPosition(3);
        $this->assertSame(3, $this->category->getPosition());
    }

    public function testSetPositionReturnsSelf(): void
    {
        $result = $this->category->setPosition(1);
        $this->assertSame($this->category, $result);
    }

    public function testDisplayOnHomeDefaultsToTrue(): void
    {
        $this->assertTrue($this->category->getDisplayOnHome());
    }

    public function testSetDisplayOnHomeToFalse(): void
    {
        $this->category->setDisplayOnHome(false);
        $this->assertFalse($this->category->getDisplayOnHome());
    }

    public function testSetDisplayOnHomeReturnsSelf(): void
    {
        $result = $this->category->setDisplayOnHome(true);
        $this->assertSame($this->category, $result);
    }

    // ------------------------------------------------------------------
    // Forums collection
    // ------------------------------------------------------------------

    public function testGetForumsReturnsCollection(): void
    {
        $this->assertInstanceOf(Collection::class, $this->category->getForums());
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToStringReturnsNameAndId(): void
    {
        $this->category->setName('News');
        $this->assertStringContainsString('News', (string) $this->category);
    }
}
