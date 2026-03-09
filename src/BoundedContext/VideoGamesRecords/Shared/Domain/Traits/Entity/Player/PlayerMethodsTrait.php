<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\Player;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;

trait PlayerMethodsTrait
{
    public function setPlayer(Player $player): static
    {
        $this->player = $player;
        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }
}
