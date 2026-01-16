<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;

class LostPositionEvent extends Event
{
    public function __construct(
        private readonly PlayerChart $playerChart,
        private readonly int $previousRank,
        private readonly int $previousNbEqual
    ) {
    }

    public function getPlayerChart(): PlayerChart
    {
        return $this->playerChart;
    }

    public function getPreviousRank(): int
    {
        return $this->previousRank;
    }

    public function getPreviousNbEqual(): int
    {
        return $this->previousNbEqual;
    }

    public function getCurrentRank(): int
    {
        return $this->playerChart->getRank();
    }

    public function hasLostPosition(): bool
    {
        return $this->getCurrentRank() > $this->previousRank;
    }
}
