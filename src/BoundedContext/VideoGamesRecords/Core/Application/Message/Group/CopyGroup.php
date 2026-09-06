<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Message\Group;

readonly class CopyGroup
{
    public function __construct(
        private int $groupId,
        private bool $withLibs,
    ) {
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function isWithLibs(): bool
    {
        return $this->withLibs;
    }

    public function getUniqueIdentifier(): string
    {
        return 'CopyGroup' . $this->groupId;
    }
}
