<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerGameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

class HomeRecentGames extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly PlayerRepository $playerRepository,
        private readonly PlayerGameRepository $playerGameRepository,
    ) {
    }

    public function __invoke(): Response
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return new Response('');
        }

        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            return new Response('');
        }

        $playerGames = $this->playerGameRepository->findAllByPlayerOrderedByLastUpdate($player, 10);

        return $this->render('@VideoGamesRecordsCore/player/_home_recent_games.html.twig', [
            'player' => $player,
            'playerGames' => $playerGames,
        ]);
    }
}
