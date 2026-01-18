<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Ranking;

use App\BoundedContext\VideoGamesRecords\Core\Application\DataProvider\Ranking\PlayerRankingProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Exception\ORMException;

class TopPlayersController extends AbstractController
{
    private PlayerRankingProvider $playerRankingProvider;

    public function __construct(PlayerRankingProvider $playerRankingProvider)
    {
        $this->playerRankingProvider = $playerRankingProvider;
    }

    /**
     * @throws ORMException
     */
    public function gamePoints(): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingPointGame([
            'maxRank' => 5,
            'limit' => 5
        ]);

        return $this->render('@VideoGamesRecordsCore/ranking/_top_players_game_points.html.twig', [
            'players' => $topPlayers
        ]);
    }

    /**
     * @throws ORMException
     */
    public function cups(): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingCup([
            'maxRank' => 5,
            'limit' => 5
        ]);

        return $this->render('@VideoGamesRecordsCore/ranking/_top_players_cups.html.twig', [
            'players' => $topPlayers
        ]);
    }

    /**
     * @throws ORMException
     */
    public function medals(): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingMedals([
            'maxRank' => 5,
            'limit' => 5
        ]);

        return $this->render('@VideoGamesRecordsCore/ranking/_top_players_medals.html.twig', [
            'players' => $topPlayers
        ]);
    }

    /**
     * @throws ORMException
     */
    public function recordPoints(): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingPointChart([
            'maxRank' => 5,
            'limit' => 5
        ]);

        return $this->render('@VideoGamesRecordsCore/ranking/_top_players_record_points.html.twig', [
            'players' => $topPlayers
        ]);
    }

    /**
     * @throws ORMException
     */
    public function badges(): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingBadge([
            'maxRank' => 5,
            'limit' => 5
        ]);

        return $this->render('@VideoGamesRecordsCore/ranking/_top_players_badges.html.twig', [
            'players' => $topPlayers
        ]);
    }

    /**
     * @throws ORMException
     */
    public function proofs(): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingProof([
            'maxRank' => 5,
            'limit' => 5
        ]);

        return $this->render('@VideoGamesRecordsCore/ranking/_top_players_proofs.html.twig', [
            'players' => $topPlayers
        ]);
    }
}
