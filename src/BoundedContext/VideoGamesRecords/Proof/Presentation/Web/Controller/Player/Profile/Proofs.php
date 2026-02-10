<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Web\Controller\Player\Profile;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player\Profile\AbstractProfileController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Proofs extends AbstractProfileController
{
    public function __construct(
        PlayerRepository $playerRepository,
        private readonly PlayerChartRepository $playerChartRepository
    ) {
        parent::__construct($playerRepository);
    }

    #[Route('/player/{id}-{slug}/proofs', name: 'vgr_player_profile_proofs', requirements: ['id' => '\d+'])]
    public function __invoke(int $id, string $slug): Response
    {
        $player = $this->getPlayer($id, $slug);

        $gameStatuses = $this->playerChartRepository->getStatusCountsByGameForPlayer($player);

        return $this->render('@VideoGamesRecordsProof/player/profile/proofs.html.twig', [
            'player' => $player,
            'gameStatuses' => $gameStatuses,
            'statuses' => PlayerChartStatusEnum::cases(),
            'current_tab' => 'proofs',
        ]);
    }
}
