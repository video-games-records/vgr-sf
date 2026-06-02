<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Team\Unit\Application\MessageHandler;

use App\BoundedContext\VideoGamesRecords\Team\Application\Message\UpdateTeamRank;
use App\BoundedContext\VideoGamesRecords\Team\Application\MessageHandler\UpdateTeamRankHandler;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class UpdateTeamRankHandlerTest extends TestCase
{
    private TeamRepository&MockObject $teamRepository;
    private UpdateTeamRankHandler $handler;

    protected function setUp(): void
    {
        $this->teamRepository = $this->createMock(TeamRepository::class);
        $this->handler = new UpdateTeamRankHandler($this->teamRepository);
    }

    private function makeTeam(int $pointChart = 0, int $pointGame = 0): Team&MockObject
    {
        $team = $this->createMock(Team::class);
        $team->method('getPointChart')->willReturn($pointChart);
        $team->method('getPointGame')->willReturn($pointGame);
        $team->method('getPointBadge')->willReturn(0);
        $team->method('getNbMasterBadge')->willReturn(0);
        $team->method('getChartRank0')->willReturn(0);
        $team->method('getChartRank1')->willReturn(0);
        $team->method('getChartRank2')->willReturn(0);
        $team->method('getChartRank3')->willReturn(0);
        $team->method('getGameRank0')->willReturn(0);
        $team->method('getGameRank1')->willReturn(0);
        $team->method('getGameRank2')->willReturn(0);
        $team->method('getGameRank3')->willReturn(0);
        return $team;
    }

    public function testInvokeCallsAllRankingMethods(): void
    {
        $teams = [$this->makeTeam(100), $this->makeTeam(200)];

        $this->teamRepository->method('findBy')->willReturn($teams);
        $this->teamRepository->expects($this->exactly(5))->method('flush');

        $this->handler->__invoke(new UpdateTeamRank());
    }

    public function testMajRankPointChartQueriesCorrectOrder(): void
    {
        $teams = [$this->makeTeam(500)];

        $this->teamRepository
            ->expects($this->once())
            ->method('findBy')
            ->with([], ['pointChart' => 'DESC'])
            ->willReturn($teams);

        $teams[0]->expects($this->once())->method('setRankPointChart')->with(1);

        $this->handler->majRankPointChart();
    }

    public function testMajRankPointGameQueriesCorrectOrder(): void
    {
        $teams = [$this->makeTeam(0, 300)];

        $this->teamRepository
            ->expects($this->once())
            ->method('findBy')
            ->with([], ['pointGame' => 'DESC'])
            ->willReturn($teams);

        $teams[0]->expects($this->once())->method('setRankPointGame')->with(1);

        $this->handler->majRankPointGame();
    }

    public function testMajRankMedalQueriesCorrectOrder(): void
    {
        $teams = [$this->makeTeam()];

        $this->teamRepository
            ->expects($this->once())
            ->method('findBy')
            ->with([], ['chartRank0' => 'DESC', 'chartRank1' => 'DESC', 'chartRank2' => 'DESC', 'chartRank3' => 'DESC'])
            ->willReturn($teams);

        $teams[0]->expects($this->once())->method('setRankMedal')->with(1);

        $this->handler->majRankMedal();
    }

    public function testMajRankBadgeQueriesCorrectOrder(): void
    {
        $teams = [$this->makeTeam()];

        $this->teamRepository
            ->expects($this->once())
            ->method('findBy')
            ->with([], ['pointBadge' => 'DESC', 'nbMasterBadge' => 'DESC'])
            ->willReturn($teams);

        $teams[0]->expects($this->once())->method('setRankBadge')->with(1);

        $this->handler->majRankBadge();
    }

    public function testMajRankCupQueriesCorrectOrder(): void
    {
        $teams = [$this->makeTeam()];

        $this->teamRepository
            ->expects($this->once())
            ->method('findBy')
            ->with([], ['gameRank0' => 'DESC', 'gameRank1' => 'DESC', 'gameRank2' => 'DESC', 'gameRank3' => 'DESC'])
            ->willReturn($teams);

        $teams[0]->expects($this->once())->method('setRankCup')->with(1);

        $this->handler->majRankCup();
    }

    public function testMajRankPointChartSetsRankOneForSingleTeam(): void
    {
        $team = $this->makeTeam(100);
        $team->expects($this->once())->method('setRankPointChart')->with(1);

        $this->teamRepository->method('findBy')->willReturn([$team]);

        $this->handler->majRankPointChart();
    }

    public function testMajRankPointChartFlushesAfterRankUpdate(): void
    {
        $this->teamRepository->method('findBy')->willReturn([]);
        $this->teamRepository->expects($this->once())->method('flush');

        $this->handler->majRankPointChart();
    }

    public function testMajRankPointChartWithEmptyTeamsStillFlushes(): void
    {
        $this->teamRepository->method('findBy')->willReturn([]);
        $this->teamRepository->expects($this->once())->method('flush');

        $this->handler->majRankPointChart();
    }
}
