<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Web\Controller\Profile;

use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\TeamBadgeRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Badges extends AbstractProfileController
{
    public function __construct(
        TeamRepository $teamRepository,
        private readonly TeamBadgeRepository $teamBadgeRepository,
        private readonly GameRepository $gameRepository
    ) {
        parent::__construct($teamRepository);
    }

    #[Route('/team/{id}-{slug}/badges', name: 'vgr_team_profile_badges', requirements: ['id' => '\d+'])]
    public function __invoke(int $id, string $slug): Response
    {
        $team = $this->getTeam($id, $slug);

        // Get badge data as scalars to avoid N+1 queries on Badge inverse OneToOne relationships
        $badgesData = $this->teamBadgeRepository->getMasterBadgesDataForTeam($team);

        // Fetch all games for these badges in one query
        $badgeIds = array_column($badgesData, 'badgeId');
        $games = [];
        if (!empty($badgeIds)) {
            $gamesList = $this->gameRepository->createQueryBuilder('g')
                ->where('g.badge IN (:badgeIds)')
                ->setParameter('badgeIds', $badgeIds)
                ->getQuery()
                ->getResult();
            foreach ($gamesList as $game) {
                $games[$game->getBadge()->getId()] = $game;
            }
        }

        return $this->render('@VideoGamesRecordsTeam/profile/badges.html.twig', [
            'team' => $team,
            'badgesData' => $badgesData,
            'games' => $games,
            'current_tab' => 'badges',
        ]);
    }
}
