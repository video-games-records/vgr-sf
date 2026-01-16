<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\Message;

readonly class UpdateTeamRank
{
    public function getUniqueIdentifier(): string
    {
        return 'UpdateTeamRank';
    }
}
