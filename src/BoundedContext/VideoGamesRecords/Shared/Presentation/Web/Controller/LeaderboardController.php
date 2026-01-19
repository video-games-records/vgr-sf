<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Presentation\Web\Controller;

use App\BoundedContext\VideoGamesRecords\Core\Application\DataProvider\Ranking\PlayerRankingProvider;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class LeaderboardController extends AbstractLocalizedController
{
    private PlayerRankingProvider $playerRankingProvider;

    public function __construct(PlayerRankingProvider $playerRankingProvider)
    {
        $this->playerRankingProvider = $playerRankingProvider;
    }

    /**
     * @throws ORMException
     */
    #[Route('/leaderboard-game-points', name: 'leaderboard_point_games')]
    public function gamePoints(): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingPointGame([
            'maxRank' => 100
        ]);

        return $this->render('@VideoGamesRecordsShared/leaderboard/game_points.html.twig', [
            'players' => $topPlayers
        ]);
    }

    /**
     * @throws ORMException
     */
    #[Route('/leaderboard-chart-points', name: 'leaderboard_point_charts')]
    public function chartPoints(): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingPointChart([
            'maxRank' => 100,
        ]);

        return $this->render('@VideoGamesRecordsShared/leaderboard/chart_points.html.twig', [
            'players' => $topPlayers
        ]);
    }

    /**
     * @throws ORMException
     */
    #[Route('/leaderboard-medals', name: 'leaderboard_medals')]
    public function medals(): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingMedals([
            'maxRank' => 100
        ]);

        return $this->render('@VideoGamesRecordsShared/leaderboard/medals.html.twig', [
            'players' => $topPlayers
        ]);
    }

    /**
     * @throws ORMException
     */
    #[Route('/leaderboard-cups', name: 'leaderboard_cups')]
    public function cups(): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingCup([
            'maxRank' => 100
        ]);

        return $this->render('@VideoGamesRecordsShared/leaderboard/cups.html.twig', [
            'players' => $topPlayers
        ]);
    }

    /**
     * @throws ORMException
     */
    #[Route('/leaderboard-badge', name: 'leaderboard_badges')]
    public function badges(): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingBadge([
            'maxRank' => 100
        ]);

        return $this->render('@VideoGamesRecordsShared/leaderboard/badges.html.twig', [
            'players' => $topPlayers
        ]);
    }

    /**
     * @throws ORMException
     */
    #[Route('/leaderboard-proofs', name: 'leaderboard_proofs')]
    public function proofs(): Response
    {
        $topPlayers = $this->playerRankingProvider->getRankingProof([
            'maxRank' => 100
        ]);

        return $this->render('@VideoGamesRecordsShared/leaderboard/proofs.html.twig', [
            'players' => $topPlayers
        ]);
    }
}
