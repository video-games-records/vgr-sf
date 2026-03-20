<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\PlayerChart;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class Show extends AbstractLocalizedController
{
    public function __construct(
        private readonly PlayerChartRepository $playerChartRepository
    ) {
    }

    #[Route(
        '/game/{id}-{slug}/group/{groupId}-{groupSlug}/chart/{chartId}-{chartSlug}/score/{playerChartId}',
        name: 'vgr_player_chart_show',
        requirements: ['id' => '\d+', 'groupId' => '\d+', 'chartId' => '\d+', 'playerChartId' => '\d+']
    )]
    public function show(
        int $id,
        string $slug,
        int $groupId,
        string $groupSlug,
        int $chartId,
        string $chartSlug,
        int $playerChartId
    ): Response {
        $playerChart = $this->playerChartRepository->find($playerChartId);

        if (!$playerChart) {
            throw $this->createNotFoundException('Score not found');
        }

        $chart = $playerChart->getChart();
        if ($chart->getId() !== $chartId || $chart->getSlug() !== $chartSlug) {
            throw $this->createNotFoundException('Score does not belong to this chart');
        }

        $group = $chart->getGroup();
        if ($group->getId() !== $groupId || $group->getSlug() !== $groupSlug) {
            throw $this->createNotFoundException('Chart does not belong to this group');
        }

        $game = $group->getGame();
        if ($game->getId() !== $id || $game->getSlug() !== $slug) {
            throw $this->createNotFoundException('Group does not belong to this game');
        }

        return $this->render('@VideoGamesRecordsCore/player_chart/show.html.twig', [
            'game' => $game,
            'group' => $group,
            'chart' => $chart,
            'playerChart' => $playerChart,
        ]);
    }
}
