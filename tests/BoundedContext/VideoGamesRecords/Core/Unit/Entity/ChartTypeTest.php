<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartType;
use PHPUnit\Framework\TestCase;

class ChartTypeTest extends TestCase
{
    private ChartType $chartType;

    protected function setUp(): void
    {
        $this->chartType = new ChartType();
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->chartType->getId());
    }

    public function testNameDefaultsToNull(): void
    {
        $this->assertNull($this->chartType->getName());
    }

    public function testSetAndGetName(): void
    {
        $result = $this->chartType->setName('Time');
        $this->assertSame('Time', $this->chartType->getName());
        $this->assertSame($this->chartType, $result);
    }

    public function testMaskDefaultsToEmptyString(): void
    {
        $this->assertSame('', $this->chartType->getMask());
    }

    public function testSetAndGetMask(): void
    {
        $result = $this->chartType->setMask('HH:MM:SS');
        $this->assertSame('HH:MM:SS', $this->chartType->getMask());
        $this->assertSame($this->chartType, $result);
    }

    public function testOrderByDefaultsToAsc(): void
    {
        $this->assertSame('ASC', $this->chartType->getOrderBy());
    }

    public function testSetAndGetOrderBy(): void
    {
        $result = $this->chartType->setOrderBy('DESC');
        $this->assertSame('DESC', $this->chartType->getOrderBy());
        $this->assertSame($this->chartType, $result);
    }

    // ------------------------------------------------------------------
    // getNbInput
    // ------------------------------------------------------------------

    public function testGetNbInputWithSinglePartMask(): void
    {
        $this->chartType->setMask('SCORE');
        $this->assertSame(1, $this->chartType->getNbInput());
    }

    public function testGetNbInputWithMultiPartMask(): void
    {
        $this->chartType->setMask('HH|MM|SS');
        $this->assertSame(3, $this->chartType->getNbInput());
    }

    public function testGetNbInputWithTwoPartMask(): void
    {
        $this->chartType->setMask('MM|SS');
        $this->assertSame(2, $this->chartType->getNbInput());
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->chartType->setName('Time');
        $this->chartType->setMask('HH:MM:SS');
        $this->chartType->setOrderBy('ASC');

        $result = (string) $this->chartType;
        $this->assertSame('Time [HH:MM:SS] ASC ()', $result);
    }
}
