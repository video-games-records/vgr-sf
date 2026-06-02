<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Article\Unit\Domain\ValueObject;

use App\BoundedContext\Article\Domain\ValueObject\ArticleStatus;
use PHPUnit\Framework\TestCase;

class ArticleStatusTest extends TestCase
{
    public function testIsPublishedReturnsTrueOnlyForPublished(): void
    {
        $this->assertTrue(ArticleStatus::PUBLISHED->isPublished());
        $this->assertFalse(ArticleStatus::UNDER_CONSTRUCTION->isPublished());
        $this->assertFalse(ArticleStatus::CANCELED->isPublished());
    }

    public function testGetStatusChoicesContainsAllThreeStatuses(): void
    {
        $choices = ArticleStatus::getStatusChoices();

        $this->assertCount(3, $choices);
        $this->assertArrayHasKey('UNDER CONSTRUCTION', $choices);
        $this->assertArrayHasKey('PUBLISHED', $choices);
        $this->assertArrayHasKey('CANCELED', $choices);
    }

    public function testGetValuesReturnsAllCaseValues(): void
    {
        $values = ArticleStatus::getValues();

        $this->assertCount(3, $values);
        $this->assertContains('UNDER CONSTRUCTION', $values);
        $this->assertContains('PUBLISHED', $values);
        $this->assertContains('CANCELED', $values);
    }

    public function testEnumValues(): void
    {
        $this->assertSame('UNDER CONSTRUCTION', ArticleStatus::UNDER_CONSTRUCTION->value);
        $this->assertSame('PUBLISHED', ArticleStatus::PUBLISHED->value);
        $this->assertSame('CANCELED', ArticleStatus::CANCELED->value);
    }
}
