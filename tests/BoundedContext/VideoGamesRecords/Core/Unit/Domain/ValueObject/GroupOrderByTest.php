<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Domain\ValueObject;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\GroupOrderBy;
use PHPUnit\Framework\TestCase;

class GroupOrderByTest extends TestCase
{
    public function testGetStatusChoicesContainsAllThreeCases(): void
    {
        $choices = GroupOrderBy::getStatusChoices();

        $this->assertCount(3, $choices);
        $this->assertArrayHasKey(GroupOrderBy::NAME->value, $choices);
        $this->assertArrayHasKey(GroupOrderBy::ID->value, $choices);
        $this->assertArrayHasKey(GroupOrderBy::CUSTOM->value, $choices);
    }

    public function testGetStatusChoicesValuesMatchKeys(): void
    {
        foreach (GroupOrderBy::getStatusChoices() as $key => $value) {
            $this->assertSame($key, $value);
        }
    }

    public function testEnumValues(): void
    {
        $this->assertSame('NAME', GroupOrderBy::NAME->value);
        $this->assertSame('ID', GroupOrderBy::ID->value);
        $this->assertSame('CUSTOM', GroupOrderBy::CUSTOM->value);
    }
}
