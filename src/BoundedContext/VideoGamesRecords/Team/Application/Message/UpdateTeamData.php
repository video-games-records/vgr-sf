<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\Message;

readonly class UpdateTeamData
{
    public function __construct(
        private int $teamId,
    ) {
    }

    public function getTeamId(): int
    {
        return $this->teamId;
    }

    public function getUniqueIdentifier(): string
    {
        return 'UpdateTeamData' . $this->teamId;
    }
}
