<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerGameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

class CloseToMasterBadge extends AbstractController
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

        $playerGames = $this->playerGameRepository->findWherePlayerIsSecond($player, 5);

        if (empty($playerGames)) {
            return new Response('');
        }

        $gameIds = [];
        foreach ($playerGames as $pg) {
            $gameId = $pg->getGame()->getId();
            if ($gameId !== null) {
                $gameIds[] = $gameId;
            }
        }

        $leaderPoints = $this->playerGameRepository->findLeaderPointsByGames($gameIds);

        $rows = [];
        foreach ($playerGames as $pg) {
            $gameId = $pg->getGame()->getId();
            $rows[] = [
                'playerGame' => $pg,
                'leaderPoints' => $gameId !== null ? ($leaderPoints[$gameId] ?? 0) : 0,
            ];
        }

        return $this->render('@VideoGamesRecordsCore/player_game/_close_to_master_badge.html.twig', [
            'rows' => $rows,
        ]);
    }
}
