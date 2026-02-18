<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Ranking;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\GameTopRanking;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameTopRankingRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerTopRankingRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class TopRanking extends AbstractLocalizedController
{
    public function __construct(
        private readonly GameTopRankingRepository $gameTopRankingRepository,
        private readonly PlayerTopRankingRepository $playerTopRankingRepository
    ) {
    }

    #[Route('/top-ranking', name: 'vgr_top_ranking')]
    public function __invoke(Request $request): Response
    {
        $type = $request->query->getString('type', GameTopRanking::PERIOD_WEEK);
        if (!in_array($type, GameTopRanking::PERIODS, true)) {
            $type = GameTopRanking::PERIOD_WEEK;
        }

        $value = $request->query->getString('value', '');
        if ($value === '') {
            $value = $this->getDefaultValue($type);
        }

        $gameRankings = $this->gameTopRankingRepository->findByPeriod($type, $value);
        $playerRankings = $this->playerTopRankingRepository->findByPeriod($type, $value);

        return $this->render('@VideoGamesRecordsCore/ranking/top_ranking.html.twig', [
            'gameRankings' => $gameRankings,
            'playerRankings' => $playerRankings,
            'type' => $type,
            'value' => $value,
        ]);
    }

    private function getDefaultValue(string $type): string
    {
        $now = new \DateTimeImmutable();

        return match ($type) {
            GameTopRanking::PERIOD_MONTH => $now->format('Y-m'),
            GameTopRanking::PERIOD_YEAR => $now->format('Y'),
            default => $now->format('o-\WW'),
        };
    }
}
