<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Domain\ValueObject;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\GameStatus;
use PHPUnit\Framework\TestCase;

class GameStatusTest extends TestCase
{
    public function testIsActiveReturnsTrueOnlyForActive(): void
    {
        $this->assertTrue(GameStatus::ACTIVE->isActive());
        $this->assertFalse(GameStatus::INACTIVE->isActive());
        $this->assertFalse(GameStatus::CREATED->isActive());
    }

    public function testIsInactiveReturnsTrueOnlyForInactive(): void
    {
        $this->assertTrue(GameStatus::INACTIVE->isInactive());
        $this->assertFalse(GameStatus::ACTIVE->isInactive());
        $this->assertFalse(GameStatus::CREATED->isInactive());
    }

    public function testGetStatusChoicesContainsAllCases(): void
    {
        $choices = GameStatus::getStatusChoices();

        $this->assertCount(6, $choices);
        $this->assertContains(GameStatus::CREATED->value, $choices);
        $this->assertContains(GameStatus::ACTIVE->value, $choices);
        $this->assertContains(GameStatus::INACTIVE->value, $choices);
    }

    public function testGetReverseStatusChoicesContainsAllCases(): void
    {
        $choices = GameStatus::getReverseStatusChoices();

        $this->assertCount(6, $choices);
        $this->assertArrayHasKey(GameStatus::ACTIVE->value, $choices);
        $this->assertArrayHasKey(GameStatus::INACTIVE->value, $choices);
        $this->assertArrayHasKey(GameStatus::CREATED->value, $choices);
    }

    public function testGetStatusChoicesAndReverseAreInverted(): void
    {
        $choices = GameStatus::getStatusChoices();
        $reverse = GameStatus::getReverseStatusChoices();

        foreach ($choices as $label => $value) {
            $this->assertArrayHasKey($value, $reverse);
            $this->assertSame($label, $reverse[$value]);
        }
    }
}
