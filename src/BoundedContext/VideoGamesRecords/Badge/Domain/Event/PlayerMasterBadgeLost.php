<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Domain\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;

class PlayerMasterBadgeLost extends Event
{
    protected PlayerBadge $playerBadge;
    protected Game $game;

    public function __construct(PlayerBadge $playerBadge, Game $game)
    {
        $this->playerBadge = $playerBadge;
        $this->game = $game;
    }

    public function getPlayerBadge(): PlayerBadge
    {
        return $this->playerBadge;
    }

    public function getGame(): Game
    {
        return $this->game;
    }
}
