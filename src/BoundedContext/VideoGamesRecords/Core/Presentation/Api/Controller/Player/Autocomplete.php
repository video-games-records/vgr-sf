<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Api\Controller\Player;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;

class Autocomplete extends AbstractController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
    ) {
    }

    #[Route('/api/players/autocomplete', name: 'vgr_api_player_autocomplete', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $q = $request->query->get('query', '');

        $players = $this->playerRepository->autocomplete($q);

        $results = array_map(fn($player) => [
            'id' => $player->getId(),
            'text' => $player->getPseudo(),
            'slug' => $player->getSlug(),
        ], $players);

        return $this->json($results);
    }
}
