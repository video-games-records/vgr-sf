<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player;

readonly class UpdatePlayerData
{
    public function __construct(
        private int $playerId,
    ) {
    }

    public function getPlayerId(): int
    {
        return $this->playerId;
    }

    public function getUniqueIdentifier(): string
    {
        return 'UpdatePlayerData' . $this->playerId;
    }
}
