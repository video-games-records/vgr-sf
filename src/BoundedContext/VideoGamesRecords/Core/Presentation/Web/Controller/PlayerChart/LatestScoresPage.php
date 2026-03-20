<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\PlayerChart;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class LatestScoresPage extends AbstractLocalizedController
{
    private const array ALLOWED_PERIODS = [1, 7, 14, 30];
    private const int DEFAULT_PERIOD = 7;
    private const int ITEMS_PER_PAGE = 30;

    public function __construct(
        private readonly PlayerChartRepository $playerChartRepository,
    ) {
    }

    #[Route('/scores/latest', name: 'vgr_latest_scores', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $period = $request->query->getInt('period', self::DEFAULT_PERIOD);
        $page = $request->query->getInt('page', 1);

        // Validate period
        if (!in_array($period, self::ALLOWED_PERIODS, true)) {
            $period = self::DEFAULT_PERIOD;
        }

        $paginator = $this->playerChartRepository->findLatestByPeriod($period, $page, self::ITEMS_PER_PAGE);
        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / self::ITEMS_PER_PAGE);

        return $this->render('@VideoGamesRecordsCore/player_chart/latest_scores.html.twig', [
            'playerCharts' => $paginator,
            'period' => $period,
            'periods' => self::ALLOWED_PERIODS,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
        ]);
    }
}
