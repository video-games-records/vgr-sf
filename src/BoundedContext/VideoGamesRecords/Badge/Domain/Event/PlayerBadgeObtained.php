<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Domain\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge;

class PlayerBadgeObtained extends Event
{
    protected PlayerBadge $playerBadge;

    public function __construct(PlayerBadge $playerBadge)
    {
        $this->playerBadge = $playerBadge;
    }

    public function getPlayerBadge(): PlayerBadge
    {
        return $this->playerBadge;
    }
}
