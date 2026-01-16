<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player;

readonly class UpdatePlayerGroupRank
{
    public function __construct(
        private int $groupId,
    ) {
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function getUniqueIdentifier(): string
    {
        return 'UpdatePlayerGroupRank' . $this->groupId;
    }
}
