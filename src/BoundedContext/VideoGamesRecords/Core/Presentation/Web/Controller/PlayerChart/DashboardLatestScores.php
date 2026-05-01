<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\PlayerChart;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

class DashboardLatestScores extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly PlayerRepository $playerRepository,
        private readonly PlayerChartRepository $playerChartRepository,
    ) {
    }

    public function __invoke(): Response
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return new Response('');
        }

        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            return new Response('');
        }

        return $this->render('@VideoGamesRecordsCore/player_chart/_dashboard_latest_scores.html.twig', [
            'playerCharts' => $this->playerChartRepository->findLatestByPlayer($player, 10),
        ]);
    }
}
