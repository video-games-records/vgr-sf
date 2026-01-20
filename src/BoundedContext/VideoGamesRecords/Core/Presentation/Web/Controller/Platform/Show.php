<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Platform;

use App\BoundedContext\VideoGamesRecords\Core\Application\DataProvider\Ranking\PlayerPlatformRankingProvider;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlatformRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Show extends AbstractLocalizedController
{
    public function __construct(
        private readonly PlatformRepository $platformRepository,
        private readonly GameRepository $gameRepository,
        private readonly PlayerPlatformRankingProvider $rankingProvider
    ) {
    }

    #[Route('/platform/{id}-{slug}', name: 'vgr_platform_show', requirements: ['id' => '\d+'])]
    public function show(int $id, string $slug, string $tab = 'games'): Response
    {
        $platform = $this->platformRepository->find($id);

        if (!$platform || $platform->getSlug() !== $slug) {
            throw $this->createNotFoundException('Platform not found');
        }

        // Get games for this platform
        $games = $platform->getGames()->toArray();

        // Sort games by name
        usort($games, fn($a, $b) => strcmp($a->getName(), $b->getName()));

        // Get leaderboard rankings
        $rankings = [];
        try {
            $rankings = $this->rankingProvider->getRankingPoints($id, ['maxRank' => 100]);
        } catch (\Exception $e) {
            // Leaderboard not available
        }

        return $this->render('@VideoGamesRecordsCore/platform/show.html.twig', [
            'platform' => $platform,
            'games' => $games,
            'rankings' => $rankings,
            'activeTab' => $tab,
        ]);
    }
}
