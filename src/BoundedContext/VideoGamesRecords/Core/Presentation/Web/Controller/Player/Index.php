<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Index extends AbstractLocalizedController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository
    ) {
    }

    #[Route('/players', name: 'vgr_player_index')]
    public function index(Request $request): Response
    {
        $sortBy = $request->query->get('sort', 'pointGame');
        $order = $request->query->get('order', 'DESC');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 100;
        $offset = ($page - 1) * $limit;

        $players = $this->playerRepository->findAllWithSort($sortBy, $order, $limit, $offset);
        $totalPlayers = $this->playerRepository->countActivePlayers();
        $totalPages = (int) ceil($totalPlayers / $limit);

        return $this->render('@VideoGamesRecordsCore/player/index.html.twig', [
            'players' => $players,
            'currentSort' => $sortBy,
            'currentOrder' => $order,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPlayers' => $totalPlayers,
        ]);
    }
}
