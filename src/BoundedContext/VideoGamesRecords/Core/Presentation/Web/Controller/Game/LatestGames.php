<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Game;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class LatestGames extends AbstractController
{
    public function __construct(private readonly GameRepository $gameRepository)
    {
    }

    public function __invoke(): Response
    {
        $games = $this->gameRepository->findLatest(5);

        return $this->render('@VideoGamesRecordsCore/game/_latest_games.html.twig', [
            'games' => $games,
        ]);
    }
}
