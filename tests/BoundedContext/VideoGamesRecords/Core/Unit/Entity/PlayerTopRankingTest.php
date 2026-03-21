<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerTopRanking;
use PHPUnit\Framework\TestCase;

class PlayerTopRankingTest extends TestCase
{
    private PlayerTopRanking $ranking;

    protected function setUp(): void
    {
        $this->ranking = new PlayerTopRanking();
    }

    // ------------------------------------------------------------------
    // Constants
    // ------------------------------------------------------------------

    public function testPeriodConstants(): void
    {
        $this->assertSame('week', PlayerTopRanking::PERIOD_WEEK);
        $this->assertSame('month', PlayerTopRanking::PERIOD_MONTH);
        $this->assertSame('year', PlayerTopRanking::PERIOD_YEAR);
    }

    public function testPeriodsArrayContainsAllPeriods(): void
    {
        $this->assertContains(PlayerTopRanking::PERIOD_WEEK, PlayerTopRanking::PERIODS);
        $this->assertContains(PlayerTopRanking::PERIOD_MONTH, PlayerTopRanking::PERIODS);
        $this->assertContains(PlayerTopRanking::PERIOD_YEAR, PlayerTopRanking::PERIODS);
        $this->assertCount(3, PlayerTopRanking::PERIODS);
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
        $result = $this->ranking->setNbPost(10);
        $this->assertSame(10, $this->ranking->getNbPost());
        $this->assertSame($this->ranking, $result);
    }

    public function testPositionChangeDefaultsToNull(): void
    {
        $this->assertNull($this->ranking->getPositionChange());
    }

    public function testSetAndGetPositionChange(): void
    {
        $result = $this->ranking->setPositionChange(5);
        $this->assertSame(5, $this->ranking->getPositionChange());
        $this->assertSame($this->ranking, $result);
    }

    public function testSetPositionChangeToNull(): void
    {
        $this->ranking->setPositionChange(5);
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
        $result = $this->ranking->setPeriodType(PlayerTopRanking::PERIOD_MONTH);
        $this->assertSame(PlayerTopRanking::PERIOD_MONTH, $this->ranking->getPeriodType());
        $this->assertSame($this->ranking, $result);
    }

    public function testSetAndGetPeriodValue(): void
    {
        $result = $this->ranking->setPeriodValue('2024-06');
        $this->assertSame('2024-06', $this->ranking->getPeriodValue());
        $this->assertSame($this->ranking, $result);
    }

    // ------------------------------------------------------------------
    // Player relation
    // ------------------------------------------------------------------

    public function testSetAndGetPlayer(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->ranking->setPlayer($player);
        $this->assertSame($player, $this->ranking->getPlayer());
        $this->assertSame($this->ranking, $result);
    }

    // ------------------------------------------------------------------
    // Period helpers
    // ------------------------------------------------------------------

    public function testSetWeekPeriod(): void
    {
        $result = $this->ranking->setWeekPeriod(2024, 12);
        $this->assertSame(PlayerTopRanking::PERIOD_WEEK, $this->ranking->getPeriodType());
        $this->assertSame('2024-W12', $this->ranking->getPeriodValue());
        $this->assertSame($this->ranking, $result);
    }

    public function testSetMonthPeriod(): void
    {
        $result = $this->ranking->setMonthPeriod(2024, 8);
        $this->assertSame(PlayerTopRanking::PERIOD_MONTH, $this->ranking->getPeriodType());
        $this->assertSame('2024-08', $this->ranking->getPeriodValue());
        $this->assertSame($this->ranking, $result);
    }

    public function testSetYearPeriod(): void
    {
        $result = $this->ranking->setYearPeriod(2023);
        $this->assertSame(PlayerTopRanking::PERIOD_YEAR, $this->ranking->getPeriodType());
        $this->assertSame('2023', $this->ranking->getPeriodValue());
        $this->assertSame($this->ranking, $result);
    }

    // ------------------------------------------------------------------
    // Period extraction
    // ------------------------------------------------------------------

    public function testGetYearFromMonthPeriod(): void
    {
        $this->ranking->setMonthPeriod(2024, 3);
        $this->assertSame(2024, $this->ranking->getYear());
    }

    public function testGetYearFromWeekPeriod(): void
    {
        $this->ranking->setWeekPeriod(2023, 10);
        $this->assertSame(2023, $this->ranking->getYear());
    }

    public function testGetYearFromYearPeriod(): void
    {
        $this->ranking->setYearPeriod(2025);
        $this->assertSame(2025, $this->ranking->getYear());
    }

    public function testGetMonthForMonthPeriod(): void
    {
        $this->ranking->setMonthPeriod(2024, 11);
        $this->assertSame(11, $this->ranking->getMonth());
    }

    public function testGetMonthReturnsNullForNonMonthPeriod(): void
    {
        $this->ranking->setWeekPeriod(2024, 1);
        $this->assertNull($this->ranking->getMonth());
    }

    public function testGetWeekForWeekPeriod(): void
    {
        $this->ranking->setWeekPeriod(2024, 22);
        $this->assertSame(22, $this->ranking->getWeek());
    }

    public function testGetWeekReturnsNullForNonWeekPeriod(): void
    {
        $this->ranking->setYearPeriod(2024);
        $this->assertNull($this->ranking->getWeek());
    }

    // ------------------------------------------------------------------
    // Position change helpers
    // ------------------------------------------------------------------

    public function testHasImprovedReturnsTrueWhenPositiveChange(): void
    {
        $this->ranking->setPositionChange(3);
        $this->assertTrue($this->ranking->hasImproved());
    }

    public function testHasImprovedReturnsFalseWhenNegativeChange(): void
    {
        $this->ranking->setPositionChange(-2);
        $this->assertFalse($this->ranking->hasImproved());
    }

    public function testHasImprovedReturnsFalseWhenNullChange(): void
    {
        $this->assertFalse($this->ranking->hasImproved());
    }

    public function testHasDeclinedReturnsTrueWhenNegativeChange(): void
    {
        $this->ranking->setPositionChange(-1);
        $this->assertTrue($this->ranking->hasDeclined());
    }

    public function testHasDeclinedReturnsFalseWhenPositiveChange(): void
    {
        $this->ranking->setPositionChange(1);
        $this->assertFalse($this->ranking->hasDeclined());
    }

    public function testHasDeclinedReturnsFalseWhenNullChange(): void
    {
        $this->assertFalse($this->ranking->hasDeclined());
    }

    public function testIsStableReturnsTrueWhenZeroChange(): void
    {
        $this->ranking->setPositionChange(0);
        $this->assertTrue($this->ranking->isStable());
    }

    public function testIsStableReturnsFalseWhenNonZeroChange(): void
    {
        $this->ranking->setPositionChange(2);
        $this->assertFalse($this->ranking->isStable());
    }

    public function testIsStableReturnsFalseWhenNullChange(): void
    {
        $this->assertFalse($this->ranking->isStable());
    }
}
