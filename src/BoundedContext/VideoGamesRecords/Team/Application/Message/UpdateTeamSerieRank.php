<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\Message;

readonly class UpdateTeamSerieRank
{
    public function __construct(
        private int $serieId,
    ) {
    }

    public function getSerieId(): int
    {
        return $this->serieId;
    }

    public function getUniqueIdentifier(): string
    {
        return 'UpdateTeamSerieRank' . $this->serieId;
    }
}
