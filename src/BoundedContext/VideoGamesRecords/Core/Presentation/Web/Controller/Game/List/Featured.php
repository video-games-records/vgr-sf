<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Game\List;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\GameStatus;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Featured extends AbstractLocalizedController
{
    public function __construct(
        private readonly GameRepository $gameRepository
    ) {
    }

    #[Route('/games/featured', name: 'vgr_game_list_featured')]
    public function list(): Response
    {
        // Get games grouped by status (non-active statuses only)
        $gamesCreated = $this->gameRepository->findBy(
            ['status' => GameStatus::CREATED],
            ['libGameEn' => 'ASC']
        );

        $gamesAddScore = $this->gameRepository->findBy(
            ['status' => GameStatus::ADD_SCORE],
            ['libGameEn' => 'ASC']
        );

        $gamesAddPicture = $this->gameRepository->findBy(
            ['status' => GameStatus::ADD_PICTURE],
            ['libGameEn' => 'ASC']
        );

        $gamesCompleted = $this->gameRepository->findBy(
            ['status' => GameStatus::COMPLETED],
            ['libGameEn' => 'ASC']
        );

        return $this->render('@VideoGamesRecordsCore/game/list_featured.html.twig', [
            'gamesCreated' => $gamesCreated,
            'gamesAddScore' => $gamesAddScore,
            'gamesAddPicture' => $gamesAddPicture,
            'gamesCompleted' => $gamesCompleted,
        ]);
    }
}
