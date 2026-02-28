<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Presentation\Api\Controller\Player;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\BoundedContext\VideoGamesRecords\Dwh\Infrastructure\Doctrine\Repository\PlayerRepository;

class GetPositionsController extends AbstractController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
    ) {
    }

    #[Route('/api/dwh/player/{id}/positions', name: 'vgr_dwh_player_positions', methods: ['GET'])]
    public function __invoke(int $id): JsonResponse
    {
        $object = $this->playerRepository->findOneBy(['id' => $id], ['date' => 'DESC']);

        if ($object === null) {
            return $this->json([]);
        }

        $positions = [];
        for ($i = 1; $i <= 30; $i++) {
            $positions[] = $object->getChartRank($i);
        }

        return $this->json($positions);
    }
}
