<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Game;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class LatestGames extends AbstractController
{
    public const string CACHE_KEY = 'latest_games';

    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly CacheInterface $cache,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function __invoke(int $ttl = 0): Response
    {
        if ($ttl > 0) {
            $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'en';
            $cacheKey = self::CACHE_KEY . '_' . $locale;
            $html = $this->cache->get($cacheKey, function (ItemInterface $item) use ($ttl) {
                $item->expiresAfter($ttl);
                $games = $this->gameRepository->findLatest(5);

                return $this->renderView('@VideoGamesRecordsCore/game/_latest_games.html.twig', [
                    'games' => $games,
                ]);
            });

            return new Response($html);
        }

        $games = $this->gameRepository->findLatest(5);

        return $this->render('@VideoGamesRecordsCore/game/_latest_games.html.twig', [
            'games' => $games,
        ]);
    }
}
