<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Presentation\Web\Controller;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\PlayerBadgeRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

class PlayerDashboardMasterBadges extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly PlayerRepository $playerRepository,
        private readonly PlayerBadgeRepository $playerBadgeRepository,
    ) {
    }

    public function __invoke(): Response
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return new Response('');
        }

        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            return new Response('');
        }

        return $this->render('@VideoGamesRecordsBadge/player/_dashboard_master_badges.html.twig', [
            'gained' => $this->playerBadgeRepository->getRecentlyGainedMasterBadges($player),
            'lost' => $this->playerBadgeRepository->getRecentlyLostMasterBadges($player),
        ]);
    }
}
