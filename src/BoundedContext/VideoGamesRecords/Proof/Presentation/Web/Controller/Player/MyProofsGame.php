<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Web\Controller\Player;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class MyProofsGame extends AbstractController
{
    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly PlayerChartRepository $playerChartRepository,
        private readonly GameRepository $gameRepository
    ) {
    }

    #[Route(
        '/my-proofs/game/{gameId}-{gameSlug}',
        name: 'vgr_my_proofs_game',
        requirements: ['gameId' => '\d+']
    )]
    #[IsGranted('ROLE_USER')]
    public function __invoke(int $gameId, string $gameSlug): Response
    {
        $player = $this->userProvider->getPlayer();

        if ($player === null) {
            throw $this->createAccessDeniedException();
        }

        $game = $this->gameRepository->find($gameId);
        if (!$game || $game->getSlug() !== $gameSlug) {
            throw new NotFoundHttpException('Game not found');
        }

        $groupedByGroup = $this->playerChartRepository->findByPlayerAndGameGroupedByGroup($player, $game);

        return $this->render('@VideoGamesRecordsProof/player/my_proofs_game.html.twig', [
            'game' => $game,
            'groupedByGroup' => $groupedByGroup,
        ]);
    }
}
