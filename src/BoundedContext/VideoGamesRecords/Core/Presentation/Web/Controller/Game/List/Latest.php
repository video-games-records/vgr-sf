<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Game\List;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class Latest extends AbstractLocalizedController
{
    public function __construct(
        private readonly GameRepository $gameRepository
    ) {
    }

    #[Route('/games/latest', name: 'vgr_game_list_latest')]
    public function list(): Response
    {
        $games = $this->gameRepository->findLatest(20);

        return $this->render('@VideoGamesRecordsCore/game/list_latest.html.twig', [
            'games' => $games,
        ]);
    }
}
