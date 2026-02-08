<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\PlayerChart;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class LatestScores extends AbstractController
{
    public const string CACHE_KEY = 'latest_scores';

    public function __construct(
        private readonly PlayerChartRepository $playerChartRepository,
        private readonly CacheInterface $cache,
    ) {
    }

    public function __invoke(int $ttl = 0): Response
    {
        if ($ttl > 0) {
            $html = $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) use ($ttl) {
                $item->expiresAfter($ttl);
                $playerCharts = $this->playerChartRepository->findLatest(6);

                return $this->renderView('@VideoGamesRecordsCore/player_chart/_latest_scores.html.twig', [
                    'playerCharts' => $playerCharts,
                ]);
            });

            return new Response($html);
        }

        $playerCharts = $this->playerChartRepository->findLatest(6);

        return $this->render('@VideoGamesRecordsCore/player_chart/_latest_scores.html.twig', [
            'playerCharts' => $playerCharts,
        ]);
    }
}
