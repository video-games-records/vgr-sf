<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\MessageHandler;

use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\BoundedContext\VideoGamesRecords\Team\Application\Message\UpdateTeamRank;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\RankingTools;

#[AsMessageHandler]
readonly class UpdateTeamRankHandler
{
    public function __construct(
        private readonly TeamRepository $teamRepository,
    ) {
    }
    public function __invoke(UpdateTeamRank $updateTeamRank): void
    {
        $this->majRankPointChart();
        $this->majRankPointGame();
        $this->majRankMedal();
        $this->majRankBadge();
        $this->majRankCup();
    }


    public function majRankPointChart(): void
    {
        $teams = $this->teamRepository->findBy([], ['pointChart' => 'DESC']);
        RankingTools::addObjectRank($teams);
        $this->teamRepository->flush();
    }

    public function majRankPointGame(): void
    {
        $teams = $this->teamRepository->findBy([], ['pointGame' => 'DESC']);
        RankingTools::addObjectRank($teams, 'rankPointGame', ['pointGame']);
        $this->teamRepository->flush();
    }

    public function majRankMedal(): void
    {
        $teams = $this->teamRepository->findBy(
            [],
            ['chartRank0' => 'DESC', 'chartRank1' => 'DESC', 'chartRank2' => 'DESC', 'chartRank3' => 'DESC']
        );
        RankingTools::addObjectRank($teams, 'rankMedal', ['chartRank0', 'chartRank1', 'chartRank2', 'chartRank3']);
        $this->teamRepository->flush();
    }

    public function majRankCup(): void
    {
        $teams = $this->teamRepository->findBy(
            [],
            ['gameRank0' => 'DESC', 'gameRank1' => 'DESC', 'gameRank2' => 'DESC', 'gameRank3' => 'DESC']
        );
        RankingTools::addObjectRank($teams, 'rankCup', ['gameRank0', 'gameRank1', 'gameRank2', 'gameRank3']);
        $this->teamRepository->flush();
    }

    public function majRankBadge(): void
    {
        $teams = $this->teamRepository->findBy([], ['pointBadge' => 'DESC', 'nbMasterBadge' => 'DESC']);
        RankingTools::addObjectRank($teams, 'rankBadge', ['pointBadge', 'nbMasterBadge']);
        $this->teamRepository->flush();
    }
}
