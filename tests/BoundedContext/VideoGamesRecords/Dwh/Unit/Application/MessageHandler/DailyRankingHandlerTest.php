<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Dwh\Unit\Application\MessageHandler;

use App\BoundedContext\VideoGamesRecords\Core\Application\Service\GameRankingService;
use App\BoundedContext\VideoGamesRecords\Core\Application\Service\PlayerRankingService;
use App\BoundedContext\VideoGamesRecords\Dwh\Application\Message\DailyRanking;
use App\BoundedContext\VideoGamesRecords\Dwh\Application\MessageHandler\DailyRankingHandler;
use DateTime;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[AllowMockObjectsWithoutExpectations]
class DailyRankingHandlerTest extends TestCase
{
    private GameRankingService&MockObject $gameRankingService;
    private PlayerRankingService&MockObject $playerRankingService;
    private LoggerInterface&MockObject $logger;
    private DailyRankingHandler $handler;

    protected function setUp(): void
    {
        $this->gameRankingService = $this->createMock(GameRankingService::class);
        $this->playerRankingService = $this->createMock(PlayerRankingService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new DailyRankingHandler(
            $this->gameRankingService,
            $this->playerRankingService,
            $this->logger
        );
    }

    public function testMondayTriggersWeeklyRankings(): void
    {
        $monday = new DateTime('2024-01-08'); // Monday

        $this->gameRankingService->expects($this->once())->method('generateWeeklyRankings')->willReturn([]);
        $this->playerRankingService->expects($this->once())->method('generateWeeklyRankings')->willReturn([]);
        $this->gameRankingService->expects($this->never())->method('generateMonthlyRankings');
        $this->gameRankingService->expects($this->never())->method('generateYearlyRankings');

        ($this->handler)(new DailyRanking($monday));
    }

    public function testTuesdaySkipsWeeklyRankings(): void
    {
        $tuesday = new DateTime('2024-01-09'); // Tuesday

        $this->gameRankingService->expects($this->never())->method('generateWeeklyRankings');
        $this->playerRankingService->expects($this->never())->method('generateWeeklyRankings');

        ($this->handler)(new DailyRanking($tuesday));
    }

    public function testFirstDayOfMonthTriggersMonthlyRankings(): void
    {
        $firstOfMonth = new DateTime('2024-02-01'); // 1st of Feb, Thursday

        $this->gameRankingService->expects($this->once())->method('generateMonthlyRankings')->willReturn([]);
        $this->playerRankingService->expects($this->once())->method('generateMonthlyRankings')->willReturn([]);

        ($this->handler)(new DailyRanking($firstOfMonth));
    }

    public function testSecondDayOfMonthSkipsMonthlyRankings(): void
    {
        $secondOfMonth = new DateTime('2024-02-02');

        $this->gameRankingService->expects($this->never())->method('generateMonthlyRankings');

        ($this->handler)(new DailyRanking($secondOfMonth));
    }

    public function testJanuaryFirstTriggersYearlyRankings(): void
    {
        $newYear = new DateTime('2024-01-01'); // January 1st, Monday (weekly + monthly + yearly)

        $this->gameRankingService->expects($this->once())->method('generateYearlyRankings')->willReturn([]);
        $this->playerRankingService->expects($this->once())->method('generateYearlyRankings')->willReturn([]);

        ($this->handler)(new DailyRanking($newYear));
    }

    public function testNonJanuaryFirstSkipsYearlyRankings(): void
    {
        $notNewYear = new DateTime('2024-06-01'); // June 1st

        $this->gameRankingService->expects($this->never())->method('generateYearlyRankings');
        $this->playerRankingService->expects($this->never())->method('generateYearlyRankings');

        ($this->handler)(new DailyRanking($notNewYear));
    }

    public function testJanuaryFirstTriggersAllRankingTypes(): void
    {
        // 2024-01-01 is a Monday → weekly + monthly + yearly all fire
        $newYear = new DateTime('2024-01-01');

        $this->gameRankingService->expects($this->once())->method('generateWeeklyRankings')->willReturn([]);
        $this->gameRankingService->expects($this->once())->method('generateMonthlyRankings')->willReturn([]);
        $this->gameRankingService->expects($this->once())->method('generateYearlyRankings')->willReturn([]);
        $this->playerRankingService->expects($this->once())->method('generateWeeklyRankings')->willReturn([]);
        $this->playerRankingService->expects($this->once())->method('generateMonthlyRankings')->willReturn([]);
        $this->playerRankingService->expects($this->once())->method('generateYearlyRankings')->willReturn([]);

        ($this->handler)(new DailyRanking($newYear));
    }

    public function testNullDateDefaultsToNow(): void
    {
        // With no date, handler should not throw
        $this->gameRankingService->method('generateWeeklyRankings')->willReturn([]);
        $this->gameRankingService->method('generateMonthlyRankings')->willReturn([]);
        $this->gameRankingService->method('generateYearlyRankings')->willReturn([]);
        $this->playerRankingService->method('generateWeeklyRankings')->willReturn([]);
        $this->playerRankingService->method('generateMonthlyRankings')->willReturn([]);
        $this->playerRankingService->method('generateYearlyRankings')->willReturn([]);

        ($this->handler)(new DailyRanking(null));

        $this->addToAssertionCount(1); // no exception = pass
    }

    public function testExceptionIsPropagatedFromRankingService(): void
    {
        $monday = new DateTime('2024-01-08');

        $this->gameRankingService->method('generateWeeklyRankings')
            ->willThrowException(new \RuntimeException('DB error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB error');

        ($this->handler)(new DailyRanking($monday));
    }
}
