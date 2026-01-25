<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Domain\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\TeamBadge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;

class TeamMasterBadgeLost extends Event
{
    protected TeamBadge $teamBadge;
    protected Game $game;

    public function __construct(TeamBadge $teamBadge, Game $game)
    {
        $this->teamBadge = $teamBadge;
        $this->game = $game;
    }

    public function getTeamBadge(): TeamBadge
    {
        return $this->teamBadge;
    }

    public function getGame(): Game
    {
        return $this->game;
    }
}
