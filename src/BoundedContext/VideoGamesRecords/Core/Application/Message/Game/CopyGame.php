<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Message\Game;

readonly class CopyGame
{
    public function __construct(
        private int $gameId,
    ) {
    }

    public function getGameId(): int
    {
        return $this->gameId;
    }

    public function getUniqueIdentifier(): string
    {
        return 'CopyGame' . $this->gameId;
    }
}
