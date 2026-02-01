<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Ranking;

use App\BoundedContext\VideoGamesRecords\Core\Application\DataProvider\Ranking\PlayerGroupRankingProvider;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\Exception\ORMException;

class PlayerGroupRankingController extends AbstractController
{
    public function __construct(
        private readonly PlayerGroupRankingProvider $playerGroupRankingProvider,
        private readonly TeamRepository $teamRepository
    ) {
    }

    /**
     * @throws ORMException
     */
    public function points(int $groupId, int $limit = 100): Response
    {
        $playerRanking = $this->playerGroupRankingProvider->getRankingPoints($groupId, [
            'maxRank' => $limit
        ]);

        return $this->render('@VideoGamesRecordsCore/ranking/_player_group_points.html.twig', [
            'playerRanking' => $playerRanking
        ]);
    }

    /**
     * @throws ORMException
     */
    public function medals(int $groupId, int $limit = 100): Response
    {
        $playerRanking = $this->playerGroupRankingProvider->getRankingMedals($groupId, [
            'maxRank' => $limit
        ]);

        return $this->render('@VideoGamesRecordsCore/ranking/_player_group_medals.html.twig', [
            'playerRanking' => $playerRanking
        ]);
    }

    #[Route('/group/{groupId}/team/{teamId}/players', name: 'vgr_group_team_players_ajax', requirements: ['groupId' => '\d+', 'teamId' => '\d+'])]
    public function teamPlayersModal(int $groupId, int $teamId): Response
    {
        $team = $this->teamRepository->find($teamId);
        if (!$team) {
            throw $this->createNotFoundException('Team not found');
        }

        $playerRankingPoints = $this->playerGroupRankingProvider->getRankingPoints($groupId, ['idTeam' => $teamId]);
        $playerRankingMedals = $this->playerGroupRankingProvider->getRankingMedals($groupId, ['idTeam' => $teamId]);

        return $this->render('@VideoGamesRecordsCore/ranking/_player_group_team_modal_content.html.twig', [
            'team' => $team,
            'playerRankingPoints' => $playerRankingPoints,
            'playerRankingMedals' => $playerRankingMedals
        ]);
    }
}
