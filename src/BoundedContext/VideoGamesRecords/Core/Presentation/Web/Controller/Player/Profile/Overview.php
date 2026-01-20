<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player\Profile;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Overview extends AbstractLocalizedController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository
    ) {
    }

    #[Route('/player/{id}-{slug}', name: 'vgr_player_profile_overview', requirements: ['id' => '\d+'])]
    public function overview(int $id, string $slug): Response
    {
        $player = $this->playerRepository->find($id);

        if (!$player || $player->getSlug() !== $slug) {
            throw $this->createNotFoundException('Player not found');
        }

        return $this->render('@VideoGamesRecordsCore/player/profile/overview.html.twig', [
            'player' => $player,
            'current_tab' => 'overview',
        ]);
    }

    #[Route('/player/{id}-{slug}/badges', name: 'vgr_player_profile_badges', requirements: ['id' => '\d+'])]
    public function badges(int $id, string $slug): Response
    {
        $player = $this->playerRepository->find($id);

        if (!$player || $player->getSlug() !== $slug) {
            throw $this->createNotFoundException('Player not found');
        }

        return $this->render('@VideoGamesRecordsCore/player/profile/badges.html.twig', [
            'player' => $player,
            'current_tab' => 'badges',
        ]);
    }

    #[Route('/player/{id}-{slug}/games', name: 'vgr_player_profile_games', requirements: ['id' => '\d+'])]
    public function games(int $id, string $slug): Response
    {
        $player = $this->playerRepository->find($id);

        if (!$player || $player->getSlug() !== $slug) {
            throw $this->createNotFoundException('Player not found');
        }

        return $this->render('@VideoGamesRecordsCore/player/profile/games.html.twig', [
            'player' => $player,
            'current_tab' => 'games',
        ]);
    }

    #[Route('/player/{id}-{slug}/proofs', name: 'vgr_player_profile_proofs', requirements: ['id' => '\d+'])]
    public function proofs(int $id, string $slug): Response
    {
        $player = $this->playerRepository->find($id);

        if (!$player || $player->getSlug() !== $slug) {
            throw $this->createNotFoundException('Player not found');
        }

        return $this->render('@VideoGamesRecordsCore/player/profile/proofs.html.twig', [
            'player' => $player,
            'current_tab' => 'proofs',
        ]);
    }
}
