<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\Game;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;

trait GameMethodsTrait
{
    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    public function getGame(): Game
    {
        return $this->game;
    }
}
