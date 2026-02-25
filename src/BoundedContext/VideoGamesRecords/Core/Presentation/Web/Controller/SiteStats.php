<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class SiteStats extends AbstractController
{
    public const string CACHE_KEY = 'site_stats_data';

    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly PlayerChartRepository $playerChartRepository,
        private readonly GameRepository $gameRepository,
        private readonly CacheInterface $cache,
    ) {
    }

    public function __invoke(int $ttl = 0): Response
    {
        $data = $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) use ($ttl) {
            $item->expiresAfter($ttl > 0 ? $ttl : 86400);

            return [
                'nbPlayers' => $this->playerRepository->countActivePlayers(),
                'nbScores' => $this->playerChartRepository->countAll(),
                'nbGames' => (int) $this->gameRepository->countStatusActive(),
            ];
        });

        return $this->render('@VideoGamesRecordsCore/_site_stats.html.twig', $data);
    }
}
