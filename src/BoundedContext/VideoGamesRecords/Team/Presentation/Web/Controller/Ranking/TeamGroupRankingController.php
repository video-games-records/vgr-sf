<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Web\Controller\Ranking;

use App\BoundedContext\VideoGamesRecords\Team\Application\DataProvider\Ranking\TeamGroupRankingProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Exception\ORMException;

class TeamGroupRankingController extends AbstractController
{
    public function __construct(
        private readonly TeamGroupRankingProvider $teamGroupRankingProvider
    ) {
    }

    /**
     * @throws ORMException
     */
    public function points(int $groupId, int $limit = 100): Response
    {
        $teamRanking = $this->teamGroupRankingProvider->getRankingPoints($groupId, [
            'maxRank' => $limit
        ]);

        return $this->render('@VideoGamesRecordsTeam/ranking/_team_group_points.html.twig', [
            'teamRanking' => $teamRanking,
            'groupId' => $groupId
        ]);
    }

    /**
     * @throws ORMException
     */
    public function medals(int $groupId, int $limit = 100): Response
    {
        $teamRanking = $this->teamGroupRankingProvider->getRankingMedals($groupId, [
            'maxRank' => $limit
        ]);

        return $this->render('@VideoGamesRecordsTeam/ranking/_team_group_medals.html.twig', [
            'teamRanking' => $teamRanking,
            'groupId' => $groupId
        ]);
    }
}
