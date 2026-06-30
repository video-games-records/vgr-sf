<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Service;

use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Game as IgdbGame;

readonly class MatchResult
{
    public function __construct(
        public ?IgdbGame $igdbGame,
        public int $candidatesCount,
    ) {
    }

    public function isMatched(): bool
    {
        return $this->igdbGame !== null;
    }

    public function isAmbiguous(): bool
    {
        return $this->igdbGame === null && $this->candidatesCount > 1;
    }
}
