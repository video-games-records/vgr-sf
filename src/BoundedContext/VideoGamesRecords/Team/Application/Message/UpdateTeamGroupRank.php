<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\Message;

readonly class UpdateTeamGroupRank
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
        return 'UpdateTeamGroupRank' . $this->groupId;
    }
}
