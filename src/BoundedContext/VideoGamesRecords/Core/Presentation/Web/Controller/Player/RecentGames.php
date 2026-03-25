<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerGameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class RecentGames extends AbstractController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly PlayerGameRepository $playerGameRepository,
    ) {
    }

    public function __invoke(int $limit = 10): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if ($user === null) {
            return $this->render('@VideoGamesRecordsCore/player/_recent_games.html.twig', [
                'playerGames' => [],
                'isLoggedIn' => false,
            ]);
        }

        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            return $this->render('@VideoGamesRecordsCore/player/_recent_games.html.twig', [
                'playerGames' => [],
                'isLoggedIn' => true,
            ]);
        }

        $playerGames = $this->playerGameRepository->findLatestByPlayer($player, $limit);

        return $this->render('@VideoGamesRecordsCore/player/_recent_games.html.twig', [
            'playerGames' => $playerGames,
            'isLoggedIn' => true,
        ]);
    }
}
