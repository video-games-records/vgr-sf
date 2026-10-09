<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player\Profile;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class LastScores extends AbstractProfileController
{
    private const int LIMIT = 50;

    public function __construct(
        PlayerRepository $playerRepository,
        private readonly PlayerChartRepository $playerChartRepository,
    ) {
        parent::__construct($playerRepository);
    }

    #[Route('/player/{id}-{slug}/last-scores', name: 'vgr_player_profile_last_scores', requirements: ['id' => '\d+'])]
    public function __invoke(int $id, string $slug): Response
    {
        $player = $this->getPlayer($id, $slug);

        return $this->render('@VideoGamesRecordsCore/player/profile/last_scores.html.twig', [
            'player' => $player,
            'playerCharts' => $this->playerChartRepository->findLastUpdatedByPlayer($player, self::LIMIT),
            'current_tab' => 'last_scores',
        ]);
    }
}
