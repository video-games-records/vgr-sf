<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player\Profile;

use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\PlayerBadgeRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlatformRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\SerieRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Badges extends AbstractProfileController
{
    public function __construct(
        PlayerRepository $playerRepository,
        private readonly PlayerBadgeRepository $playerBadgeRepository,
        private readonly GameRepository $gameRepository,
        private readonly PlatformRepository $platformRepository,
        private readonly SerieRepository $serieRepository
    ) {
        parent::__construct($playerRepository);
    }

    #[Route('/player/{id}-{slug}/badges', name: 'vgr_player_profile_badges', requirements: ['id' => '\d+'])]
    public function __invoke(int $id, string $slug): Response
    {
        $player = $this->getPlayer($id, $slug);

        // Get master badge data as scalars to avoid N+1 queries
        $masterBadgesData = $this->playerBadgeRepository->getMasterBadgesDataForPlayer($player);

        // Fetch all games for master badges in one query
        $masterBadgeIds = array_column($masterBadgesData, 'badgeId');
        $games = [];
        if (!empty($masterBadgeIds)) {
            $gamesList = $this->gameRepository->createQueryBuilder('g')
                ->where('g.badge IN (:badgeIds)')
                ->setParameter('badgeIds', $masterBadgeIds)
                ->getQuery()
                ->getResult();
            foreach ($gamesList as $game) {
                $games[$game->getBadge()->getId()] = $game;
            }
        }

        // Get platform badge data as scalars to avoid N+1 queries
        $platformBadgesData = $this->playerBadgeRepository->getPlatformBadgesDataForPlayer($player);

        // Fetch all platforms for platform badges in one query
        $platformBadgeIds = array_column($platformBadgesData, 'badgeId');
        $platforms = [];
        if (!empty($platformBadgeIds)) {
            $platformsList = $this->platformRepository->createQueryBuilder('p')
                ->where('p.badge IN (:badgeIds)')
                ->setParameter('badgeIds', $platformBadgeIds)
                ->getQuery()
                ->getResult();
            foreach ($platformsList as $platform) {
                $platforms[$platform->getBadge()->getId()] = $platform;
            }
        }

        // Get serie badge data as scalars to avoid N+1 queries
        $serieBadgesData = $this->playerBadgeRepository->getSerieBadgesDataForPlayer($player);

        // Fetch all series for serie badges in one query
        $serieBadgeIds = array_column($serieBadgesData, 'badgeId');
        $series = [];
        if (!empty($serieBadgeIds)) {
            $seriesList = $this->serieRepository->createQueryBuilder('s')
                ->where('s.badge IN (:badgeIds)')
                ->setParameter('badgeIds', $serieBadgeIds)
                ->getQuery()
                ->getResult();
            foreach ($seriesList as $serie) {
                $series[$serie->getBadge()->getId()] = $serie;
            }
        }

        // Get simple badge types (no entity association needed)
        $forumBadgesData = $this->playerBadgeRepository->getForumBadgesDataForPlayer($player);
        $connexionBadgesData = $this->playerBadgeRepository->getConnexionBadgesDataForPlayer($player);
        $chartBadgesData = $this->playerBadgeRepository->getChartBadgesDataForPlayer($player);
        $proofBadgesData = $this->playerBadgeRepository->getProofBadgesDataForPlayer($player);
        $donationBadgesData = $this->playerBadgeRepository->getDonationBadgesDataForPlayer($player);

        return $this->render('@VideoGamesRecordsCore/player/profile/badges.html.twig', [
            'player' => $player,
            'masterBadgesData' => $masterBadgesData,
            'games' => $games,
            'platformBadgesData' => $platformBadgesData,
            'platforms' => $platforms,
            'serieBadgesData' => $serieBadgesData,
            'series' => $series,
            'forumBadgesData' => $forumBadgesData,
            'connexionBadgesData' => $connexionBadgesData,
            'chartBadgesData' => $chartBadgesData,
            'proofBadgesData' => $proofBadgesData,
            'donationBadgesData' => $donationBadgesData,
            'current_tab' => 'badges',
        ]);
    }
}
