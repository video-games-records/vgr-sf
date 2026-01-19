<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Game;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\BoundedContext\VideoGamesRecords\Core\Application\Manager\GameOfDayManager;

class GameOfDay extends AbstractController
{
    public function __construct(private readonly GameOfDayManager $gameOfDayManager)
    {
    }

    public function __invoke(): Response
    {
        $game = $this->gameOfDayManager->getGameOfDay();

        return $this->render('@VideoGamesRecordsCore/game/_game_of_day.html.twig', [
            'game' => $game
        ]);
    }
}
