<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Presentation\Web\Controller;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\TeamBadgeRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
#[IsGranted('ROLE_USER')]
class ManageTeamBadges extends AbstractLocalizedController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly TeamBadgeRepository $teamBadgeRepository,
    ) {
    }

    #[Route('/team/badges/manage', name: 'vgr_team_badges_manage', methods: ['GET'])]
    public function manage(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        if (!$player || !$player->getTeam() || !$player->isLeader()) {
            throw new AccessDeniedHttpException('You must be a team leader to manage team badges.');
        }

        return $this->render('@VideoGamesRecordsBadge/team/badges/manage.html.twig', [
            'player' => $player,
            'team' => $player->getTeam(),
            'masterBadgesData' => $this->teamBadgeRepository->getMasterBadgesForManagement($player->getTeam()),
        ]);
    }

    #[Route('/team/badges/reorder', name: 'vgr_team_badges_reorder', methods: ['POST'])]
    public function reorder(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        if (!$player || !$player->getTeam() || !$player->isLeader()) {
            return new JsonResponse(['error' => 'Access denied.'], Response::HTTP_FORBIDDEN);
        }

        /** @var array<int, int>|null $order */
        $order = json_decode($request->getContent(), true)['order'] ?? null;

        if (!is_array($order)) {
            return new JsonResponse(['error' => 'Invalid payload.'], Response::HTTP_BAD_REQUEST);
        }

        $this->teamBadgeRepository->updateMasterBadgesOrder($player->getTeam(), $order);

        return new JsonResponse(['success' => true]);
    }
}
