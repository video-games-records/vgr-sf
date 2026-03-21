<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerSerie;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use PHPUnit\Framework\TestCase;

class PlayerSerieTest extends TestCase
{
    private PlayerSerie $playerSerie;

    protected function setUp(): void
    {
        $this->playerSerie = new PlayerSerie();
    }

    // ------------------------------------------------------------------
    // Basic properties (trait defaults)
    // ------------------------------------------------------------------

    public function testRankMedalDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerSerie->getRankMedal());
    }

    public function testSetAndGetRankMedal(): void
    {
        $this->playerSerie->setRankMedal(3);
        $this->assertSame(3, $this->playerSerie->getRankMedal());
    }

    public function testChartRank0DefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerSerie->getChartRank0());
    }

    public function testSetAndGetChartRank0(): void
    {
        $this->playerSerie->setChartRank0(10);
        $this->assertSame(10, $this->playerSerie->getChartRank0());
    }

    public function testRankPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerSerie->getRankPointChart());
    }

    public function testSetAndGetRankPointChart(): void
    {
        $this->playerSerie->setRankPointChart(50);
        $this->assertSame(50, $this->playerSerie->getRankPointChart());
    }

    public function testPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerSerie->getPointChart());
    }

    public function testSetAndGetPointChart(): void
    {
        $this->playerSerie->setPointChart(400);
        $this->assertSame(400, $this->playerSerie->getPointChart());
    }

    public function testNbChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerSerie->getNbChart());
    }

    public function testNbChartProvenDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerSerie->getNbChartProven());
    }

    public function testNbGameDefaultsToZero(): void
    {
        $this->assertSame(0, $this->playerSerie->getNbGame());
    }

    public function testSetAndGetNbGame(): void
    {
        $this->playerSerie->setNbGame(5);
        $this->assertSame(5, $this->playerSerie->getNbGame());
    }

    // ------------------------------------------------------------------
    // Own properties
    // ------------------------------------------------------------------

    public function testSetAndGetPointChartWithoutDlc(): void
    {
        $result = $this->playerSerie->setPointChartWithoutDlc(150);
        $this->assertSame(150, $this->playerSerie->getPointChartWithoutDlc());
        $this->assertSame($this->playerSerie, $result);
    }

    public function testSetAndGetPointGame(): void
    {
        $result = $this->playerSerie->setPointGame(800);
        $this->assertSame(800, $this->playerSerie->getPointGame());
        $this->assertSame($this->playerSerie, $result);
    }

    // ------------------------------------------------------------------
    // Player relation
    // ------------------------------------------------------------------

    public function testSetAndGetPlayer(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->playerSerie->setPlayer($player);
        $this->assertSame($player, $this->playerSerie->getPlayer());
        $this->assertSame($this->playerSerie, $result);
    }

    // ------------------------------------------------------------------
    // Serie relation
    // ------------------------------------------------------------------

    public function testSetAndGetSerie(): void
    {
        $serie = $this->createMock(Serie::class);
        $result = $this->playerSerie->setSerie($serie);
        $this->assertSame($serie, $this->playerSerie->getSerie());
        $this->assertSame($this->playerSerie, $result);
    }

    // ------------------------------------------------------------------
    // getMedalsBackgroundColor
    // ------------------------------------------------------------------

    public function testGetMedalsBackgroundColorForFirstPlace(): void
    {
        $this->playerSerie->setRankMedal(1);
        $this->assertSame('class="bg-first"', $this->playerSerie->getMedalsBackgroundColor());
    }

    public function testGetMedalsBackgroundColorForSecondPlace(): void
    {
        $this->playerSerie->setRankMedal(2);
        $this->assertSame('class="bg-second"', $this->playerSerie->getMedalsBackgroundColor());
    }

    public function testGetMedalsBackgroundColorForThirdPlace(): void
    {
        $this->playerSerie->setRankMedal(3);
        $this->assertSame('class="bg-third"', $this->playerSerie->getMedalsBackgroundColor());
    }

    public function testGetMedalsBackgroundColorForBeyondThird(): void
    {
        $this->playerSerie->setRankMedal(4);
        $this->assertSame('', $this->playerSerie->getMedalsBackgroundColor());
    }

    public function testGetMedalsBackgroundColorForZeroRank(): void
    {
        $this->playerSerie->setRankMedal(0);
        $this->assertSame('class=""', $this->playerSerie->getMedalsBackgroundColor());
    }
}
