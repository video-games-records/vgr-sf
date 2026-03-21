<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartType;
use PHPUnit\Framework\TestCase;

class ChartLibTest extends TestCase
{
    private ChartLib $chartLib;

    protected function setUp(): void
    {
        $this->chartLib = new ChartLib();
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->chartLib->getId());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->chartLib->setId(10);
        $this->assertSame(10, $this->chartLib->getId());
        $this->assertSame($this->chartLib, $result);
    }

    public function testNameDefaultsToNull(): void
    {
        $this->assertNull($this->chartLib->getName());
    }

    public function testSetAndGetName(): void
    {
        $result = $this->chartLib->setName('Time');
        $this->assertSame('Time', $this->chartLib->getName());
        $this->assertSame($this->chartLib, $result);
    }

    public function testSetNameToNull(): void
    {
        $this->chartLib->setName('Time');
        $this->chartLib->setName(null);
        $this->assertNull($this->chartLib->getName());
    }

    // ------------------------------------------------------------------
    // Chart relation
    // ------------------------------------------------------------------

    public function testSetAndGetChart(): void
    {
        $chart = $this->createMock(Chart::class);
        $result = $this->chartLib->setChart($chart);
        $this->assertSame($chart, $this->chartLib->getChart());
        $this->assertSame($this->chartLib, $result);
    }

    // ------------------------------------------------------------------
    // ChartType relation
    // ------------------------------------------------------------------

    public function testSetAndGetType(): void
    {
        $type = $this->createMock(ChartType::class);
        $result = $this->chartLib->setType($type);
        $this->assertSame($type, $this->chartLib->getType());
        $this->assertSame($this->chartLib, $result);
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $type = $this->createMock(ChartType::class);
        $type->method('getName')->willReturn('Time');

        $this->chartLib->setId(3);
        $this->chartLib->setType($type);

        $this->assertSame('Time [3]', (string) $this->chartLib);
    }
}
