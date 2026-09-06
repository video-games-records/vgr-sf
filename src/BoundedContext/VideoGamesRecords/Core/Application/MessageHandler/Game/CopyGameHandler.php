<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\MessageHandler\Game;

use App\BoundedContext\VideoGamesRecords\Core\Application\Manager\GameManager;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Game\CopyGame;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\SharedKernel\Domain\Exception\EntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CopyGameHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private GameManager $gameManager,
    ) {
    }

    public function __invoke(CopyGame $copyGame): void
    {
        /** @var Game|null $game */
        $game = $this->em->getRepository(Game::class)->find($copyGame->getGameId());
        if (null === $game) {
            throw new EntityNotFoundException('Game', $copyGame->getGameId());
        }

        $this->gameManager->copy($game);
    }
}
