<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Ranking;

use App\BoundedContext\VideoGamesRecords\Core\Application\DataProvider\Ranking\PlayerGameRankingProvider;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\Exception\ORMException;

class PlayerGameRankingController extends AbstractController
{
    public function __construct(
        private readonly PlayerGameRankingProvider $playerGameRankingProvider,
        private readonly TeamRepository $teamRepository
    ) {
    }

    /**
     * @throws ORMException
     */
    public function points(int $gameId, int $limit = 100): Response
    {
        $options = ['maxRank' => $limit];

        $playerRanking = $this->playerGameRankingProvider->getRankingPoints($gameId, $options);

        return $this->render('@VideoGamesRecordsCore/ranking/_player_game_points.html.twig', [
            'playerRanking' => $playerRanking
        ]);
    }

    /**
     * @throws ORMException
     */
    public function medals(int $gameId, int $limit = 100): Response
    {
        $options = ['maxRank' => $limit];

        $playerRanking = $this->playerGameRankingProvider->getRankingMedals($gameId, $options);

        return $this->render('@VideoGamesRecordsCore/ranking/_player_game_medals.html.twig', [
            'playerRanking' => $playerRanking
        ]);
    }

    #[Route('/game/{gameId}/team/{teamId}/players', name: 'vgr_game_team_players_ajax', requirements: ['gameId' => '\d+', 'teamId' => '\d+'])]
    public function teamPlayersModal(int $gameId, int $teamId): Response
    {
        $team = $this->teamRepository->find($teamId);
        if (!$team) {
            throw $this->createNotFoundException('Team not found');
        }

        $playerRankingPoints = $this->playerGameRankingProvider->getRankingPoints($gameId, ['idTeam' => $teamId]);
        $playerRankingMedals = $this->playerGameRankingProvider->getRankingMedals($gameId, ['idTeam' => $teamId]);

        return $this->render('@VideoGamesRecordsCore/ranking/_player_game_team_modal_content.html.twig', [
            'team' => $team,
            'playerRankingPoints' => $playerRankingPoints,
            'playerRankingMedals' => $playerRankingMedals
        ]);
    }
}
