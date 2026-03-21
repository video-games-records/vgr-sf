<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use DateTime;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PlayerChartTest extends TestCase
{
    private PlayerChart $playerChart;

    protected function setUp(): void
    {
        $this->playerChart = new PlayerChart();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyLibsCollection(): void
    {
        $pc = new PlayerChart();
        $this->assertInstanceOf(Collection::class, $pc->getLibs());
        $this->assertCount(0, $pc->getLibs());
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->playerChart->getId());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->playerChart->setId(10);
        $this->assertSame(10, $this->playerChart->getId());
        $this->assertSame($this->playerChart, $result);
    }

    public function testRankDefaultsToNull(): void
    {
        $this->assertNull($this->playerChart->getRank());
    }

    public function testSetAndGetRank(): void
    {
        $result = $this->playerChart->setRank(1);
        $this->assertSame(1, $this->playerChart->getRank());
        $this->assertSame($this->playerChart, $result);
    }

    public function testPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerChart->getPointChart());
    }

    public function testSetAndGetPointChart(): void
    {
        $result = $this->playerChart->setPointChart(100);
        $this->assertSame(100, $this->playerChart->getPointChart());
        $this->assertSame($this->playerChart, $result);
    }

    public function testPointPlatformDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerChart->getPointPlatform());
    }

    public function testSetAndGetPointPlatform(): void
    {
        $result = $this->playerChart->setPointPlatform(50);
        $this->assertSame(50, $this->playerChart->getPointPlatform());
        $this->assertSame($this->playerChart, $result);
    }

    public function testIsTopScoreDefaultsToFalse(): void
    {
        $this->assertFalse($this->playerChart->getIsTopScore());
    }

    public function testSetAndGetIsTopScore(): void
    {
        $result = $this->playerChart->setIsTopScore(true);
        $this->assertTrue($this->playerChart->getIsTopScore());
        $this->assertSame($this->playerChart, $result);
    }

    public function testDateInvestigationDefaultsToNull(): void
    {
        $this->assertNull($this->playerChart->getDateInvestigation());
    }

    public function testSetAndGetDateInvestigation(): void
    {
        $date = new DateTime('2024-06-01');
        $result = $this->playerChart->setDateInvestigation($date);
        $this->assertSame($date, $this->playerChart->getDateInvestigation());
        $this->assertSame($this->playerChart, $result);
    }

    public function testSetDateInvestigationToNull(): void
    {
        $this->playerChart->setDateInvestigation(new DateTime());
        $this->playerChart->setDateInvestigation(null);
        $this->assertNull($this->playerChart->getDateInvestigation());
    }

    public function testNbEqualDefaultsToOne(): void
    {
        $this->assertSame(1, $this->playerChart->getNbEqual());
    }

    public function testSetAndGetNbEqual(): void
    {
        $this->playerChart->setNbEqual(3);
        $this->assertSame(3, $this->playerChart->getNbEqual());
    }

    // ------------------------------------------------------------------
    // Status
    // ------------------------------------------------------------------

    public function testStatusDefaultsToNone(): void
    {
        $this->assertSame(PlayerChartStatusEnum::NONE, $this->playerChart->getStatus());
    }

    public function testSetAndGetStatus(): void
    {
        $result = $this->playerChart->setStatus(PlayerChartStatusEnum::PROVED);
        $this->assertSame(PlayerChartStatusEnum::PROVED, $this->playerChart->getStatus());
        $this->assertSame($this->playerChart, $result);
    }

    #[DataProvider('statusLabelProvider')]
    public function testGetStatusLabel(PlayerChartStatusEnum $status, string $expectedLabel): void
    {
        $this->playerChart->setStatus($status);
        $this->assertSame($expectedLabel, $this->playerChart->getStatusLabel());
    }

    /**
     * @return array<string, array{PlayerChartStatusEnum, string}>
     */
    public static function statusLabelProvider(): array
    {
        return [
            'NONE' => [PlayerChartStatusEnum::NONE, 'None'],
            'REQUEST_PENDING' => [PlayerChartStatusEnum::REQUEST_PENDING, 'Request Pending'],
            'REQUEST_VALIDATED' => [PlayerChartStatusEnum::REQUEST_VALIDATED, 'Request Validated'],
            'PROOF_SENT' => [PlayerChartStatusEnum::PROOF_SENT, 'Proof Sent'],
            'PROVED' => [PlayerChartStatusEnum::PROVED, 'Proved'],
            'UNPROVED' => [PlayerChartStatusEnum::UNPROVED, 'Unproved'],
        ];
    }

    // ------------------------------------------------------------------
    // Chart relation
    // ------------------------------------------------------------------

    public function testSetAndGetChart(): void
    {
        $chart = $this->createMock(Chart::class);
        $result = $this->playerChart->setChart($chart);
        $this->assertSame($chart, $this->playerChart->getChart());
        $this->assertSame($this->playerChart, $result);
    }

    // ------------------------------------------------------------------
    // Player relation
    // ------------------------------------------------------------------

    public function testSetAndGetPlayer(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->playerChart->setPlayer($player);
        $this->assertSame($player, $this->playerChart->getPlayer());
        $this->assertSame($this->playerChart, $result);
    }

    // ------------------------------------------------------------------
    // Proof relation
    // ------------------------------------------------------------------

    public function testProofDefaultsToNull(): void
    {
        $this->assertNull($this->playerChart->getProof());
    }

    public function testSetAndGetProof(): void
    {
        $proof = $this->createMock(Proof::class);
        $result = $this->playerChart->setProof($proof);
        $this->assertSame($proof, $this->playerChart->getProof());
        $this->assertSame($this->playerChart, $result);
    }

    public function testSetProofToNull(): void
    {
        $proof = $this->createMock(Proof::class);
        $this->playerChart->setProof($proof);
        $this->playerChart->setProof(null);
        $this->assertNull($this->playerChart->getProof());
    }

    // ------------------------------------------------------------------
    // Platform relation
    // ------------------------------------------------------------------

    public function testPlatformDefaultsToNull(): void
    {
        $this->assertNull($this->playerChart->getPlatform());
    }

    public function testSetAndGetPlatform(): void
    {
        $platform = $this->createMock(Platform::class);
        $result = $this->playerChart->setPlatform($platform);
        $this->assertSame($platform, $this->playerChart->getPlatform());
        $this->assertSame($this->playerChart, $result);
    }

    public function testSetPlatformToNull(): void
    {
        $platform = $this->createMock(Platform::class);
        $this->playerChart->setPlatform($platform);
        $this->playerChart->setPlatform(null);
        $this->assertNull($this->playerChart->getPlatform());
    }

    // ------------------------------------------------------------------
    // Libs collection
    // ------------------------------------------------------------------

    public function testAddLib(): void
    {
        $lib = $this->createMock(PlayerChartLib::class);
        $lib->expects($this->once())->method('setPlayerChart')->with($this->playerChart);

        $this->playerChart->addLib($lib);

        $this->assertCount(1, $this->playerChart->getLibs());
    }

    public function testRemoveLib(): void
    {
        $lib = new PlayerChartLib();
        $this->playerChart->addLib($lib);
        $this->playerChart->removeLib($lib);

        $this->assertCount(0, $this->playerChart->getLibs());
    }

    // ------------------------------------------------------------------
    // getValuesAsString
    // ------------------------------------------------------------------

    public function testGetValuesAsStringWithNoLibs(): void
    {
        $this->assertSame('', $this->playerChart->getValuesAsString());
    }

    public function testGetValuesAsStringWithOneLib(): void
    {
        $lib = $this->createMock(PlayerChartLib::class);
        $lib->method('setPlayerChart')->willReturnSelf();
        $lib->method('getValue')->willReturn('100');

        $this->playerChart->addLib($lib);

        $this->assertSame('100', $this->playerChart->getValuesAsString());
    }

    public function testGetValuesAsStringWithMultipleLibs(): void
    {
        $lib1 = $this->createMock(PlayerChartLib::class);
        $lib1->method('setPlayerChart')->willReturnSelf();
        $lib1->method('getValue')->willReturn('1');

        $lib2 = $this->createMock(PlayerChartLib::class);
        $lib2->method('setPlayerChart')->willReturnSelf();
        $lib2->method('getValue')->willReturn('30');

        $lib3 = $this->createMock(PlayerChartLib::class);
        $lib3->method('setPlayerChart')->willReturnSelf();
        $lib3->method('getValue')->willReturn('45');

        $this->playerChart->addLib($lib1);
        $this->playerChart->addLib($lib2);
        $this->playerChart->addLib($lib3);

        $this->assertSame('1|30|45', $this->playerChart->getValuesAsString());
    }
}
