<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\GameTopRanking;
use PHPUnit\Framework\TestCase;

class GameTopRankingTest extends TestCase
{
    private GameTopRanking $ranking;

    protected function setUp(): void
    {
        $this->ranking = new GameTopRanking();
    }

    // ------------------------------------------------------------------
    // Constants
    // ------------------------------------------------------------------

    public function testPeriodConstants(): void
    {
        $this->assertSame('week', GameTopRanking::PERIOD_WEEK);
        $this->assertSame('month', GameTopRanking::PERIOD_MONTH);
        $this->assertSame('year', GameTopRanking::PERIOD_YEAR);
    }

    public function testPeriodsArrayContainsAllPeriods(): void
    {
        $this->assertContains(GameTopRanking::PERIOD_WEEK, GameTopRanking::PERIODS);
        $this->assertContains(GameTopRanking::PERIOD_MONTH, GameTopRanking::PERIODS);
        $this->assertContains(GameTopRanking::PERIOD_YEAR, GameTopRanking::PERIODS);
        $this->assertCount(3, GameTopRanking::PERIODS);
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->ranking->getId());
    }

    public function testNbPostDefaultsToZero(): void
    {
        $this->assertSame(0, $this->ranking->getNbPost());
    }

    public function testSetAndGetNbPost(): void
    {
        $result = $this->ranking->setNbPost(5);
        $this->assertSame(5, $this->ranking->getNbPost());
        $this->assertSame($this->ranking, $result);
    }

    public function testPositionChangeDefaultsToNull(): void
    {
        $this->assertNull($this->ranking->getPositionChange());
    }

    public function testSetAndGetPositionChange(): void
    {
        $result = $this->ranking->setPositionChange(3);
        $this->assertSame(3, $this->ranking->getPositionChange());
        $this->assertSame($this->ranking, $result);
    }

    public function testSetPositionChangeToNull(): void
    {
        $this->ranking->setPositionChange(3);
        $this->ranking->setPositionChange(null);
        $this->assertNull($this->ranking->getPositionChange());
    }

    public function testSetAndGetRank(): void
    {
        $result = $this->ranking->setRank(1);
        $this->assertSame(1, $this->ranking->getRank());
        $this->assertSame($this->ranking, $result);
    }

    public function testSetAndGetPeriodType(): void
    {
        $result = $this->ranking->setPeriodType(GameTopRanking::PERIOD_WEEK);
        $this->assertSame(GameTopRanking::PERIOD_WEEK, $this->ranking->getPeriodType());
        $this->assertSame($this->ranking, $result);
    }

    public function testSetAndGetPeriodValue(): void
    {
        $result = $this->ranking->setPeriodValue('2024-01');
        $this->assertSame('2024-01', $this->ranking->getPeriodValue());
        $this->assertSame($this->ranking, $result);
    }

    // ------------------------------------------------------------------
    // Game relation
    // ------------------------------------------------------------------

    public function testSetAndGetGame(): void
    {
        $game = $this->createMock(Game::class);
        $result = $this->ranking->setGame($game);
        $this->assertSame($game, $this->ranking->getGame());
        $this->assertSame($this->ranking, $result);
    }

    // ------------------------------------------------------------------
    // Period helpers
    // ------------------------------------------------------------------

    public function testSetWeekPeriod(): void
    {
        $result = $this->ranking->setWeekPeriod(2024, 5);
        $this->assertSame(GameTopRanking::PERIOD_WEEK, $this->ranking->getPeriodType());
        $this->assertSame('2024-W05', $this->ranking->getPeriodValue());
        $this->assertSame($this->ranking, $result);
    }

    public function testSetMonthPeriod(): void
    {
        $result = $this->ranking->setMonthPeriod(2024, 3);
        $this->assertSame(GameTopRanking::PERIOD_MONTH, $this->ranking->getPeriodType());
        $this->assertSame('2024-03', $this->ranking->getPeriodValue());
        $this->assertSame($this->ranking, $result);
    }

    public function testSetYearPeriod(): void
    {
        $result = $this->ranking->setYearPeriod(2024);
        $this->assertSame(GameTopRanking::PERIOD_YEAR, $this->ranking->getPeriodType());
        $this->assertSame('2024', $this->ranking->getPeriodValue());
        $this->assertSame($this->ranking, $result);
    }

    // ------------------------------------------------------------------
    // Period extraction
    // ------------------------------------------------------------------

    public function testGetYearFromWeekPeriod(): void
    {
        $this->ranking->setWeekPeriod(2024, 5);
        $this->assertSame(2024, $this->ranking->getYear());
    }

    public function testGetYearFromMonthPeriod(): void
    {
        $this->ranking->setMonthPeriod(2023, 11);
        $this->assertSame(2023, $this->ranking->getYear());
    }

    public function testGetYearFromYearPeriod(): void
    {
        $this->ranking->setYearPeriod(2022);
        $this->assertSame(2022, $this->ranking->getYear());
    }

    public function testGetMonthForMonthPeriod(): void
    {
        $this->ranking->setMonthPeriod(2024, 7);
        $this->assertSame(7, $this->ranking->getMonth());
    }

    public function testGetMonthReturnsNullForNonMonthPeriod(): void
    {
        $this->ranking->setWeekPeriod(2024, 5);
        $this->assertNull($this->ranking->getMonth());
    }

    public function testGetWeekForWeekPeriod(): void
    {
        $this->ranking->setWeekPeriod(2024, 15);
        $this->assertSame(15, $this->ranking->getWeek());
    }

    public function testGetWeekReturnsNullForNonWeekPeriod(): void
    {
        $this->ranking->setMonthPeriod(2024, 6);
        $this->assertNull($this->ranking->getWeek());
    }

    // ------------------------------------------------------------------
    // Position change helpers
    // ------------------------------------------------------------------

    public function testHasImprovedReturnsTrueWhenPositiveChange(): void
    {
        $this->ranking->setPositionChange(2);
        $this->assertTrue($this->ranking->hasImproved());
    }

    public function testHasImprovedReturnsFalseWhenNegativeChange(): void
    {
        $this->ranking->setPositionChange(-1);
        $this->assertFalse($this->ranking->hasImproved());
    }

    public function testHasImprovedReturnsFalseWhenNullChange(): void
    {
        $this->ranking->setPositionChange(null);
        $this->assertFalse($this->ranking->hasImproved());
    }

    public function testHasDeclinedReturnsTrueWhenNegativeChange(): void
    {
        $this->ranking->setPositionChange(-3);
        $this->assertTrue($this->ranking->hasDeclined());
    }

    public function testHasDeclinedReturnsFalseWhenPositiveChange(): void
    {
        $this->ranking->setPositionChange(1);
        $this->assertFalse($this->ranking->hasDeclined());
    }

    public function testHasDeclinedReturnsFalseWhenNullChange(): void
    {
        $this->ranking->setPositionChange(null);
        $this->assertFalse($this->ranking->hasDeclined());
    }

    public function testIsStableReturnsTrueWhenZeroChange(): void
    {
        $this->ranking->setPositionChange(0);
        $this->assertTrue($this->ranking->isStable());
    }

    public function testIsStableReturnsFalseWhenNonZeroChange(): void
    {
        $this->ranking->setPositionChange(1);
        $this->assertFalse($this->ranking->isStable());
    }

    public function testIsStableReturnsFalseWhenNullChange(): void
    {
        $this->ranking->setPositionChange(null);
        $this->assertFalse($this->ranking->isStable());
    }
}
