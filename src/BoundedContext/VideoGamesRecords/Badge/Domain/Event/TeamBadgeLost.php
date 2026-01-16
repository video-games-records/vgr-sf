<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Domain\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\TeamBadge;

class TeamBadgeLost extends Event
{
    protected TeamBadge $teamBadge;

    public function __construct(TeamBadge $teamBadge)
    {
        $this->teamBadge = $teamBadge;
    }

    public function getTeamBadge(): TeamBadge
    {
        return $this->teamBadge;
    }
}
