<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChartLib;
use PHPUnit\Framework\TestCase;

class PlayerChartLibTest extends TestCase
{
    private PlayerChartLib $playerChartLib;

    protected function setUp(): void
    {
        $this->playerChartLib = new PlayerChartLib();
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->playerChartLib->getId());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->playerChartLib->setId(5);
        $this->assertSame(5, $this->playerChartLib->getId());
        $this->assertSame($this->playerChartLib, $result);
    }

    public function testSetAndGetValueAsString(): void
    {
        $result = $this->playerChartLib->setValue('12345');
        $this->assertSame('12345', $this->playerChartLib->getValue());
        $this->assertSame($this->playerChartLib, $result);
    }

    public function testSetValueFromInt(): void
    {
        $this->playerChartLib->setValue(999);
        $this->assertSame('999', $this->playerChartLib->getValue());
    }

    public function testSetValueWithNullDoesNotSetValue(): void
    {
        // When null is passed, the value should not be changed (initial state not set)
        $result = $this->playerChartLib->setValue(null);
        $this->assertSame($this->playerChartLib, $result);
    }

    // ------------------------------------------------------------------
    // LibChart relation
    // ------------------------------------------------------------------

    public function testSetAndGetLibChart(): void
    {
        $libChart = $this->createMock(ChartLib::class);
        $result = $this->playerChartLib->setLibChart($libChart);
        $this->assertSame($libChart, $this->playerChartLib->getLibChart());
        $this->assertSame($this->playerChartLib, $result);
    }

    // ------------------------------------------------------------------
    // PlayerChart relation
    // ------------------------------------------------------------------

    public function testSetAndGetPlayerChart(): void
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $result = $this->playerChartLib->setPlayerChart($playerChart);
        $this->assertSame($playerChart, $this->playerChartLib->getPlayerChart());
        $this->assertSame($this->playerChartLib, $result);
    }

    // ------------------------------------------------------------------
    // ParseValue
    // ------------------------------------------------------------------

    public function testSetParseValue(): void
    {
        $parseValue = ['hours' => 1, 'minutes' => 30, 'seconds' => 0];
        $result = $this->playerChartLib->setParseValue($parseValue);
        $this->assertSame($this->playerChartLib, $result);
    }
}
