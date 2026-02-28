<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Presentation\Api\Controller\Player;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\BoundedContext\VideoGamesRecords\Dwh\Infrastructure\Doctrine\Repository\PlayerRepository;

class GetMedalsByTimeController extends AbstractController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
    ) {
    }

    #[Route('/api/dwh/player/{id}/medals-by-time', name: 'vgr_dwh_player_medals_by_time', methods: ['GET'])]
    public function __invoke(int $id): JsonResponse
    {
        $list = $this->playerRepository->findBy(['id' => $id], ['date' => 'ASC']);

        if (empty($list)) {
            return $this->json([
                'rank0' => [],
                'rank1' => [],
                'rank2' => [],
                'rank3' => [],
                'dates' => [],
            ]);
        }

        $result = [
            'rank0' => [],
            'rank1' => [],
            'rank2' => [],
            'rank3' => [],
            'dates' => [],
        ];

        foreach ($list as $object) {
            $result['rank0'][] = $object->getChartRank0();
            $result['rank1'][] = $object->getChartRank1();
            $result['rank2'][] = $object->getChartRank2();
            $result['rank3'][] = $object->getChartRank3();
            $result['dates'][] = $object->getDate();
        }

        return $this->json($result);
    }
}
