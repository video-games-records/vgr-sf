<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerGameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class GameHistory extends AbstractController
{
    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly PlayerGameRepository $playerGameRepository
    ) {
    }

    #[Route('/game-history', name: 'vgr_game_history')]
    #[IsGranted('ROLE_USER')]
    public function __invoke(): Response
    {
        $player = $this->userProvider->getPlayer();

        if ($player === null) {
            throw $this->createAccessDeniedException();
        }

        $recentGames = $this->playerGameRepository->findAllByPlayerOrderedByLastUpdate($player, 20);

        return $this->render('@VideoGamesRecordsCore/player/game_history.html.twig', [
            'recentGames' => $recentGames,
        ]);
    }
}
