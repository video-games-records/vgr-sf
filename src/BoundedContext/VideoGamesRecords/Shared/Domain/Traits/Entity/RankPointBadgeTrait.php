<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;

trait RankPointBadgeTrait
{
    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $rankBadge = 0;

    public function setRankBadge(int $rankBadge): static
    {
        $this->rankBadge = $rankBadge;
        return $this;
    }

    public function getRankBadge(): int
    {
        return $this->rankBadge;
    }
}
