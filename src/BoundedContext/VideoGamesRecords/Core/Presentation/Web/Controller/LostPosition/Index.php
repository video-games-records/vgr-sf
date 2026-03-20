<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\LostPosition;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\LostPositionRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Core\Presentation\Form\LostPositionFilterType;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class Index extends AbstractLocalizedController
{
    private const int ITEMS_PER_PAGE = 20;

    public function __construct(
        private readonly LostPositionRepository $lostPositionRepository,
        private readonly PlayerRepository $playerRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/lost-positions', name: 'vgr_lost_positions', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            throw $this->createAccessDeniedException();
        }

        $page = $request->query->getInt('page', 1);
        $gameId = $request->query->get('game') ? (int) $request->query->get('game') : null;

        // Get games with lost positions for the filter dropdown
        $games = $this->lostPositionRepository->getGamesWithLostPositions($player);

        // Create form
        $form = $this->createForm(LostPositionFilterType::class, null, [
            'games' => $games,
            'locale' => $request->getLocale(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $gameId = $form->get('game')->getData();
        }

        // Get paginated lost positions
        $result = $this->lostPositionRepository->findByPlayerPaginated($player, $gameId, $page, self::ITEMS_PER_PAGE);

        // Calculate new count (positions created after lastDisplayLostPosition)
        $newCount = $this->lostPositionRepository->getNbNewLostPosition($player);

        // Update lastDisplayLostPosition
        $player->setLastDisplayLostPosition(new DateTime());
        $this->entityManager->flush();

        return $this->render('@VideoGamesRecordsCore/lost_position/index.html.twig', [
            'form' => $form->createView(),
            'lostPositions' => $result['items'],
            'currentPage' => $page,
            'totalPages' => $result['pages'],
            'totalCount' => $result['total'],
            'newCount' => $newCount,
            'currentGameId' => $gameId,
        ]);
    }
}
