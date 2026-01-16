<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\Message;

readonly class UpdateTeamGameRank
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
        return 'UpdateTeamGameRank' . $this->gameId;
    }
}
