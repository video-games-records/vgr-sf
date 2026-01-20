<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Game;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Show extends AbstractLocalizedController
{
    public function __construct(
        private readonly GameRepository $gameRepository
    ) {
    }

    #[Route('/game/{id}-{slug}', name: 'vgr_game_show', requirements: ['id' => '\d+'])]
    public function show(int $id, string $slug): Response
    {
        $game = $this->gameRepository->find($id);

        if (!$game || $game->getSlug() !== $slug) {
            throw $this->createNotFoundException('Game not found');
        }

        return $this->render('@VideoGamesRecordsCore/game/show.html.twig', [
            'game' => $game,
        ]);
    }
}
