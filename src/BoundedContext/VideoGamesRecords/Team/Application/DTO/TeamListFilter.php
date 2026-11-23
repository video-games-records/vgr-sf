<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\DTO;

final readonly class TeamListFilter
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?int $minPlayers = null,
        public ?int $pointsMin = null,
        public ?int $pointsMax = null,
        public bool $hasBadges = false,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->search === null
            && $this->status === null
            && $this->minPlayers === null
            && $this->pointsMin === null
            && $this->pointsMax === null
            && !$this->hasBadges;
    }
}
