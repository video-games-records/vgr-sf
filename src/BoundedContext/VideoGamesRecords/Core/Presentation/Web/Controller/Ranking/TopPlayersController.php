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
    public function gamePoints(?int $teamId = null, int $limit = 5, bool $showNbGame = false, int $ttl = 0): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingPointGame([
            'maxRank' => $limit,
            'limit' => $limit,
            'idTeam' => $teamId
        ]);

        $response = $this->render('@VideoGamesRecordsCore/ranking/_top_players_game_points.html.twig', [
            'players' => $topPlayers,
            'showNbGame' => $showNbGame
        ]);

        if ($ttl > 0) {
            $response->setPublic();
            $response->setSharedMaxAge($ttl);
        }

        return $response;
    }

    /**
     * @throws ORMException
     */
    public function cups(?int $teamId = null, int $limit = 5, int $ttl = 0): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingCup([
            'maxRank' => $limit,
            'limit' => $limit,
            'idTeam' => $teamId
        ]);

        $response = $this->render('@VideoGamesRecordsCore/ranking/_top_players_cups.html.twig', [
            'players' => $topPlayers
        ]);

        if ($ttl > 0) {
            $response->setPublic();
            $response->setSharedMaxAge($ttl);
        }

        return $response;
    }

    /**
     * @throws ORMException
     */
    public function medals(?int $teamId = null, int $limit = 5, int $ttl = 0): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingMedals([
            'maxRank' => $limit,
            'limit' => $limit,
            'idTeam' => $teamId
        ]);

        $response = $this->render('@VideoGamesRecordsCore/ranking/_top_players_medals.html.twig', [
            'players' => $topPlayers
        ]);

        if ($ttl > 0) {
            $response->setPublic();
            $response->setSharedMaxAge($ttl);
        }

        return $response;
    }

    /**
     * @throws ORMException
     */
    public function recordPoints(?int $teamId = null, int $limit = 5, bool $showNbChart = false, int $ttl = 0): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingPointChart([
            'maxRank' => $limit,
            'limit' => $limit,
            'idTeam' => $teamId
        ]);

        $response = $this->render('@VideoGamesRecordsCore/ranking/_top_players_record_points.html.twig', [
            'players' => $topPlayers,
            'showNbChart' => $showNbChart
        ]);

        if ($ttl > 0) {
            $response->setPublic();
            $response->setSharedMaxAge($ttl);
        }

        return $response;
    }

    /**
     * @throws ORMException
     */
    public function badges(?int $teamId = null, int $limit = 5, int $ttl = 0): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingBadge([
            'maxRank' => $limit,
            'limit' => $limit,
            'idTeam' => $teamId
        ]);

        $response = $this->render('@VideoGamesRecordsCore/ranking/_top_players_badges.html.twig', [
            'players' => $topPlayers
        ]);

        if ($ttl > 0) {
            $response->setPublic();
            $response->setSharedMaxAge($ttl);
        }

        return $response;
    }

    /**
     * @throws ORMException
     */
    public function proofs(?int $teamId = null, int $limit = 5, bool $showPercentage = false, int $ttl = 0): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingProof([
            'maxRank' => $limit,
            'limit' => $limit,
            'idTeam' => $teamId
        ]);

        $response = $this->render('@VideoGamesRecordsCore/ranking/_top_players_proofs.html.twig', [
            'players' => $topPlayers,
            'showPercentage' => $showPercentage
        ]);

        if ($ttl > 0) {
            $response->setPublic();
            $response->setSharedMaxAge($ttl);
        }

        return $response;
    }
}
