<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Forum\Unit\Domain\ValueObject;

use App\BoundedContext\Forum\Domain\ValueObject\ForumStatus;
use PHPUnit\Framework\TestCase;

class ForumStatusTest extends TestCase
{
    public function testIsPublicReturnsTrueOnlyForPublic(): void
    {
        $this->assertTrue(ForumStatus::PUBLIC->isPublic());
        $this->assertFalse(ForumStatus::PRIVATE->isPublic());
    }

    public function testIsPrivateReturnsTrueOnlyForPrivate(): void
    {
        $this->assertTrue(ForumStatus::PRIVATE->isPrivate());
        $this->assertFalse(ForumStatus::PUBLIC->isPrivate());
    }

    public function testGetStatusChoicesContainsBothStatuses(): void
    {
        $choices = ForumStatus::getStatusChoices();

        $this->assertArrayHasKey('public', $choices);
        $this->assertArrayHasKey('private', $choices);
        $this->assertCount(2, $choices);
    }

    public function testEnumValues(): void
    {
        $this->assertSame('public', ForumStatus::PUBLIC->value);
        $this->assertSame('private', ForumStatus::PRIVATE->value);
    }
}
