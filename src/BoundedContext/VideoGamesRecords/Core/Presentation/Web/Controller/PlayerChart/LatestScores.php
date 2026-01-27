<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\PlayerChart;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class LatestScores extends AbstractController
{
    public function __construct(private readonly PlayerChartRepository $playerChartRepository)
    {
    }

    public function __invoke(): Response
    {
        $playerCharts = $this->playerChartRepository->findLatest(6);

        return $this->render('@VideoGamesRecordsCore/player_chart/_latest_scores.html.twig', [
            'playerCharts' => $playerCharts,
        ]);
    }
}
