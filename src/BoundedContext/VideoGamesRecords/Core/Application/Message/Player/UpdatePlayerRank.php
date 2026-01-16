<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player;

readonly class UpdatePlayerRank
{
    public function getUniqueIdentifier(): string
    {
        return 'UpdatePlayerRank';
    }
}
