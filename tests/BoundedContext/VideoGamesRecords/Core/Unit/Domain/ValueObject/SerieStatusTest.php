<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Domain\ValueObject;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\SerieStatus;
use PHPUnit\Framework\TestCase;

class SerieStatusTest extends TestCase
{
    public function testIsActiveReturnsTrueOnlyForActive(): void
    {
        $this->assertTrue(SerieStatus::ACTIVE->isActive());
        $this->assertFalse(SerieStatus::INACTIVE->isActive());
    }

    public function testIsInactiveReturnsTrueOnlyForInactive(): void
    {
        $this->assertTrue(SerieStatus::INACTIVE->isInactive());
        $this->assertFalse(SerieStatus::ACTIVE->isInactive());
    }

    public function testGetStatusChoicesContainsBothCases(): void
    {
        $choices = SerieStatus::getStatusChoices();

        $this->assertCount(2, $choices);
        $this->assertArrayHasKey(SerieStatus::ACTIVE->value, $choices);
        $this->assertArrayHasKey(SerieStatus::INACTIVE->value, $choices);
    }

    public function testGetStatusChoicesValuesMatchKeys(): void
    {
        foreach (SerieStatus::getStatusChoices() as $key => $value) {
            $this->assertSame($key, $value);
        }
    }
}
