<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Web\Controller\Ranking;

use App\BoundedContext\VideoGamesRecords\Team\Application\DataProvider\Ranking\TeamGameRankingProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Exception\ORMException;

class TeamGameRankingController extends AbstractController
{
    public function __construct(
        private readonly TeamGameRankingProvider $teamGameRankingProvider
    ) {
    }

    /**
     * @throws ORMException
     */
    public function points(int $gameId, int $limit = 100): Response
    {
        $teamRanking = $this->teamGameRankingProvider->getRankingPoints($gameId, [
            'maxRank' => $limit
        ]);

        return $this->render('@VideoGamesRecordsTeam/ranking/_team_game_points.html.twig', [
            'teamRanking' => $teamRanking,
            'gameId' => $gameId
        ]);
    }

    /**
     * @throws ORMException
     */
    public function medals(int $gameId, int $limit = 100): Response
    {
        $teamRanking = $this->teamGameRankingProvider->getRankingMedals($gameId, [
            'maxRank' => $limit
        ]);

        return $this->render('@VideoGamesRecordsTeam/ranking/_team_game_medals.html.twig', [
            'teamRanking' => $teamRanking,
            'gameId' => $gameId
        ]);
    }
}
