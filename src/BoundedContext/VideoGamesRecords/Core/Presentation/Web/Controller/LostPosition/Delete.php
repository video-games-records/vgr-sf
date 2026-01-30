<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\LostPosition;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\LostPositionRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Delete extends AbstractLocalizedController
{
    public function __construct(
        private readonly LostPositionRepository $lostPositionRepository,
        private readonly PlayerRepository $playerRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/lost-positions/delete', name: 'vgr_lost_positions_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function __invoke(Request $request): Response
    {
        // Verify CSRF token
        if (!$this->isCsrfTokenValid('delete-lost-positions', $request->request->getString('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('vgr_lost_positions');
        }

        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        /** @var array<int> $selectedPositions */
        $selectedPositions = $request->request->all('selected_positions');

        if (empty($selectedPositions)) {
            $message = $this->translator->trans('lost_position.flash.no_selection', [], 'VgrCore');
            $this->addFlash('warning', $message);
            return $this->redirectToRoute('vgr_lost_positions');
        }

        // Convert to integers for safety
        $ids = array_map('intval', $selectedPositions);

        $deletedCount = $this->lostPositionRepository->deleteByIdsForPlayer($ids, $player);

        $message = $this->translator->trans('lost_position.flash.deleted', ['%count%' => $deletedCount], 'VgrCore');
        $this->addFlash('success', $message);

        return $this->redirectToRoute('vgr_lost_positions');
    }
}
