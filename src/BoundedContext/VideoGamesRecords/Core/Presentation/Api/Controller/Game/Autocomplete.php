<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Api\Controller\Game;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class Autocomplete extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository,
    ) {
    }

    #[Route('/api/games/autocomplete', name: 'vgr_api_game_autocomplete', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $q = $request->query->get('query', '');
        $locale = $request->query->get('locale', 'en');

        $games = $this->gameRepository->autocomplete($q, $locale);

        $results = array_map(fn($game) => [
            'id' => $game->getId(),
            'text' => $game->getName($locale),
        ], $games);

        return $this->json($results);
    }
}
