<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Forum\Unit\Entity;

use App\BoundedContext\Forum\Domain\Entity\TopicType;
use PHPUnit\Framework\TestCase;

class TopicTypeTest extends TestCase
{
    private TopicType $topicType;

    protected function setUp(): void
    {
        $this->topicType = new TopicType();
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->topicType->getId());
    }

    public function testSetAndGetName(): void
    {
        $this->topicType->setName('Announcement');
        $this->assertSame('Announcement', $this->topicType->getName());
    }

    public function testSetNameReturnsSelf(): void
    {
        $result = $this->topicType->setName('Sticky');
        $this->assertSame($this->topicType, $result);
    }

    public function testPositionDefaultsToZero(): void
    {
        $this->assertSame(0, $this->topicType->getPosition());
    }

    public function testSetAndGetPosition(): void
    {
        $this->topicType->setPosition(2);
        $this->assertSame(2, $this->topicType->getPosition());
    }

    public function testSetPositionReturnsSelf(): void
    {
        $result = $this->topicType->setPosition(1);
        $this->assertSame($this->topicType, $result);
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToStringContainsName(): void
    {
        $this->topicType->setName('Sticky');
        $this->assertStringContainsString('Sticky', (string) $this->topicType);
    }
}
