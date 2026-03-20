<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Web\Controller\Player\Profile;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player\Profile\AbstractProfileController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class ProofsGame extends AbstractProfileController
{
    public function __construct(
        PlayerRepository $playerRepository,
        private readonly PlayerChartRepository $playerChartRepository,
        private readonly GameRepository $gameRepository
    ) {
        parent::__construct($playerRepository);
    }

    #[Route(
        '/player/{id}-{slug}/proofs/game/{gameId}-{gameSlug}',
        name: 'vgr_player_profile_proofs_game',
        requirements: ['id' => '\d+', 'gameId' => '\d+']
    )]
    public function __invoke(int $id, string $slug, int $gameId, string $gameSlug): Response
    {
        $player = $this->getPlayer($id, $slug);

        $game = $this->gameRepository->find($gameId);
        if (!$game || $game->getSlug() !== $gameSlug) {
            throw new NotFoundHttpException('Game not found');
        }

        $groupedByGroup = $this->playerChartRepository->findByPlayerAndGameGroupedByGroup($player, $game);

        return $this->render('@VideoGamesRecordsProof/player/profile/proofs_game.html.twig', [
            'player' => $player,
            'game' => $game,
            'groupedByGroup' => $groupedByGroup,
            'current_tab' => 'proofs',
        ]);
    }
}
