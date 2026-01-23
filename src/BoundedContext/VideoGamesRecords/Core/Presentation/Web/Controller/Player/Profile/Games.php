<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player\Profile;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerGameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Games extends AbstractProfileController
{
    public function __construct(
        PlayerRepository $playerRepository,
        private readonly PlayerGameRepository $playerGameRepository
    ) {
        parent::__construct($playerRepository);
    }

    #[Route('/player/{id}-{slug}/games', name: 'vgr_player_profile_games', requirements: ['id' => '\d+'])]
    public function __invoke(int $id, string $slug, Request $request): Response
    {
        $player = $this->getPlayer($id, $slug);

        $allowedSorts = ['game', 'medals', 'pointChart', 'rank'];
        $sort = $request->query->get('sort', 'pointChart');
        $order = $request->query->get('order', 'DESC');

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'pointChart';
        }

        $playerGames = $this->playerGameRepository->findByPlayerSorted($player, $sort, $order);

        return $this->render('@VideoGamesRecordsCore/player/profile/games.html.twig', [
            'player' => $player,
            'playerGames' => $playerGames,
            'currentSort' => $sort,
            'currentOrder' => $order,
            'current_tab' => 'games',
        ]);
    }
}
