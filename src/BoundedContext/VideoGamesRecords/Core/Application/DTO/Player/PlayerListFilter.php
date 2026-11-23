<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Player;

use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;

final readonly class PlayerListFilter
{
    /**
     * @param list<PlatformEnum> $platforms
     */
    public function __construct(
        public ?string $search = null,
        public ?int $countryId = null,
        public ?int $teamId = null,
        public array $platforms = [],
        public ?int $pointsMin = null,
        public ?int $pointsMax = null,
        public ?\DateTimeInterface $lastActiveSince = null,
        public bool $neverActive = false,
        public bool $hasBadges = false,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->search === null
            && $this->countryId === null
            && $this->teamId === null
            && $this->platforms === []
            && $this->pointsMin === null
            && $this->pointsMax === null
            && $this->lastActiveSince === null
            && !$this->neverActive
            && !$this->hasBadges;
    }
}
