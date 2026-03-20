<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Web\Controller\Profile;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\TeamBadgeRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRepository;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRequestRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class Badges extends AbstractProfileController
{
    public function __construct(
        TeamRepository $teamRepository,
        PlayerRepository $playerRepository,
        TeamRequestRepository $teamRequestRepository,
        private readonly TeamBadgeRepository $teamBadgeRepository,
        private readonly GameRepository $gameRepository
    ) {
        parent::__construct($teamRepository, $playerRepository, $teamRequestRepository);
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

        $user = $this->getUser();
        $isLeader = false;
        if ($user instanceof User) {
            $currentPlayer = $this->playerRepository->getPlayerFromUser($user);
            $isLeader = $currentPlayer !== null && $currentPlayer->getTeam()?->getId() === $team->getId() && $currentPlayer->isLeader();
        }

        return $this->render('@VideoGamesRecordsTeam/profile/badges.html.twig', array_merge(
            $this->getBaseParams($team, 'badges'),
            [
                'badgesData' => $badgesData,
                'games' => $games,
                'isLeader' => $isLeader,
            ]
        ));
    }
}
