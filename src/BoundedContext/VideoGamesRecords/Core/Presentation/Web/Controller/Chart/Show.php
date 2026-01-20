<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Chart;

use App\BoundedContext\VideoGamesRecords\Core\Application\DataProvider\Ranking\PlayerChartRankingProvider;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\ChartRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Show extends AbstractLocalizedController
{
    public function __construct(
        private readonly ChartRepository $chartRepository,
        private readonly PlayerChartRankingProvider $playerChartRankingProvider
    ) {
    }

    #[Route('/game/{id}-{slug}/group/{groupId}-{groupSlug}/chart/{chartId}-{chartSlug}', name: 'vgr_chart_show', requirements: ['id' => '\d+', 'groupId' => '\d+', 'chartId' => '\d+'])]
    public function show(int $id, string $slug, int $groupId, string $groupSlug, int $chartId, string $chartSlug): Response
    {
        $chart = $this->chartRepository->find($chartId);

        if (!$chart || $chart->getSlug() !== $chartSlug) {
            throw $this->createNotFoundException('Chart not found');
        }

        // Verify that the chart belongs to the specified group and game
        $group = $chart->getGroup();
        if ($group->getId() !== $groupId || $group->getSlug() !== $groupSlug) {
            throw $this->createNotFoundException('Chart does not belong to this group');
        }

        $game = $group->getGame();
        if ($game->getId() !== $id || $game->getSlug() !== $slug) {
            throw $this->createNotFoundException('Group does not belong to this game');
        }

        $ranking = $this->playerChartRankingProvider->getRanking(
            $chart,
            [
                'maxRank' => 1000,
            ]
        );

        return $this->render('@VideoGamesRecordsCore/chart/show.html.twig', [
            'game' => $game,
            'group' => $group,
            'chart' => $chart,
            'ranking' => $ranking,
        ]);
    }
}
