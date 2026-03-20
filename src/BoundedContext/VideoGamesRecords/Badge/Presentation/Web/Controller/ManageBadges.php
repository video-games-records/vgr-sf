<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Presentation\Web\Controller;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\PlayerBadgeRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
#[IsGranted('ROLE_USER')]
class ManageBadges extends AbstractLocalizedController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly PlayerBadgeRepository $playerBadgeRepository,
    ) {
    }

    #[Route('/player/badges/manage', name: 'vgr_player_badges_manage', methods: ['GET'])]
    public function manage(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        if (!$player) {
            throw new AccessDeniedHttpException('No player profile found.');
        }

        return $this->render('@VideoGamesRecordsBadge/player/badges/manage.html.twig', [
            'player' => $player,
            'masterBadgesData' => $this->playerBadgeRepository->getMasterBadgesForManagement($player),
        ]);
    }

    #[Route('/player/badges/reorder', name: 'vgr_player_badges_reorder', methods: ['POST'])]
    public function reorder(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        if (!$player) {
            return new JsonResponse(['error' => 'No player profile found.'], Response::HTTP_FORBIDDEN);
        }

        /** @var array<int, int>|null $order */
        $order = json_decode($request->getContent(), true)['order'] ?? null;

        if (!is_array($order)) {
            return new JsonResponse(['error' => 'Invalid payload.'], Response::HTTP_BAD_REQUEST);
        }

        $this->playerBadgeRepository->updateMasterBadgesOrder($player, $order);

        return new JsonResponse(['success' => true]);
    }
}
