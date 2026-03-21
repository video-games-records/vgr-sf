<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\LostPosition;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class ChartTest extends TestCase
{
    private Chart $chart;

    protected function setUp(): void
    {
        $this->chart = new Chart();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyCollections(): void
    {
        $chart = new Chart();

        $this->assertInstanceOf(Collection::class, $chart->getLibs());
        $this->assertCount(0, $chart->getLibs());

        $this->assertInstanceOf(Collection::class, $chart->getPlayerCharts());
        $this->assertCount(0, $chart->getPlayerCharts());
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->chart->getId());
    }

    public function testSetAndGetId(): void
    {
        $this->chart->setId(42);
        $this->assertSame(42, $this->chart->getId());
    }

    public function testLibChartEnDefaultsToEmptyString(): void
    {
        $this->assertSame('', $this->chart->getLibChartEn());
    }

    public function testSetAndGetLibChartEn(): void
    {
        $this->chart->setLibChartEn('Speed Run');
        $this->assertSame('Speed Run', $this->chart->getLibChartEn());
    }

    public function testLibChartFrDefaultsToEmptyString(): void
    {
        $this->assertSame('', $this->chart->getLibChartFr());
    }

    public function testSetAndGetLibChartFr(): void
    {
        $result = $this->chart->setLibChartFr('Course Rapide');
        $this->assertSame('Course Rapide', $this->chart->getLibChartFr());
        $this->assertSame($this->chart, $result);
    }

    public function testSetLibChartFrWithNullDoesNotChange(): void
    {
        $this->chart->setLibChartFr('Course Rapide');
        $this->chart->setLibChartFr(null);
        $this->assertSame('Course Rapide', $this->chart->getLibChartFr());
    }

    public function testIsProofVideoOnlyDefaultsToFalse(): void
    {
        $this->assertFalse($this->chart->getIsProofVideoOnly());
    }

    public function testSetAndGetIsProofVideoOnly(): void
    {
        $result = $this->chart->setIsProofVideoOnly(true);
        $this->assertTrue($this->chart->getIsProofVideoOnly());
        $this->assertSame($this->chart, $result);
    }

    public function testNbPostDefaultsToZero(): void
    {
        $this->assertSame(0, $this->chart->getNbPost());
    }

    public function testSetAndGetNbPost(): void
    {
        $this->chart->setNbPost(5);
        $this->assertSame(5, $this->chart->getNbPost());
    }

    public function testIsDlcDefaultsToFalse(): void
    {
        $this->assertFalse($this->chart->getIsDlc());
    }

    public function testSetAndGetIsDlc(): void
    {
        $this->chart->setIsDlc(true);
        $this->assertTrue($this->chart->getIsDlc());
    }

    // ------------------------------------------------------------------
    // Group relation
    // ------------------------------------------------------------------

    public function testSetAndGetGroup(): void
    {
        $group = $this->createMock(Group::class);
        $result = $this->chart->setGroup($group);
        $this->assertSame($group, $this->chart->getGroup());
        $this->assertSame($this->chart, $result);
    }

    // ------------------------------------------------------------------
    // PlayerChart1 / PlayerChartP shortcuts
    // ------------------------------------------------------------------

    public function testPlayerChart1DefaultsToNull(): void
    {
        $this->assertNull($this->chart->getPlayerChart1());
    }

    public function testSetAndGetPlayerChart1(): void
    {
        $pc = $this->createMock(PlayerChart::class);
        $result = $this->chart->setPlayerChart1($pc);
        $this->assertSame($pc, $this->chart->getPlayerChart1());
        $this->assertSame($this->chart, $result);
    }

    public function testSetPlayerChart1ToNull(): void
    {
        $pc = $this->createMock(PlayerChart::class);
        $this->chart->setPlayerChart1($pc);
        $this->chart->setPlayerChart1(null);
        $this->assertNull($this->chart->getPlayerChart1());
    }

    public function testPlayerChartPDefaultsToNull(): void
    {
        $this->assertNull($this->chart->getPlayerChartP());
    }

    public function testSetAndGetPlayerChartP(): void
    {
        $pc = $this->createMock(PlayerChart::class);
        $result = $this->chart->setPlayerChartP($pc);
        $this->assertSame($pc, $this->chart->getPlayerChartP());
        $this->assertSame($this->chart, $result);
    }

    // ------------------------------------------------------------------
    // Libs collection
    // ------------------------------------------------------------------

    public function testAddLib(): void
    {
        $lib = $this->createMock(ChartLib::class);
        $lib->expects($this->once())->method('setChart')->with($this->chart);

        $this->chart->addLib($lib);

        $this->assertCount(1, $this->chart->getLibs());
    }

    public function testRemoveLib(): void
    {
        $lib = new ChartLib();

        $this->chart->addLib($lib);
        $this->chart->removeLib($lib);

        $this->assertCount(0, $this->chart->getLibs());
    }

    // ------------------------------------------------------------------
    // PlayerCharts collection
    // ------------------------------------------------------------------

    public function testAddPlayerChart(): void
    {
        $pc = $this->createMock(PlayerChart::class);
        $this->chart->addPlayerChart($pc);

        $this->assertCount(1, $this->chart->getPlayerCharts());
        $this->assertTrue($this->chart->getPlayerCharts()->contains($pc));
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testGetDefaultName(): void
    {
        $this->chart->setLibChartEn('Speed Run');
        $this->assertSame('Speed Run', $this->chart->getDefaultName());
    }

    public function testGetSluggableFields(): void
    {
        $this->assertSame(['defaultName'], $this->chart->getSluggableFields());
    }

    public function testToString(): void
    {
        $this->chart->setId(7);
        $this->chart->setLibChartEn('Speed Run');
        $this->assertSame('Speed Run [7]', (string) $this->chart);
    }
}
