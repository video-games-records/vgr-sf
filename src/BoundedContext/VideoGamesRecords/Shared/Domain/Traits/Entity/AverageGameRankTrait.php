<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;

trait AverageGameRankTrait
{
    #[ORM\Column(nullable: true)]
    private ?float $averageGameRank = null;

    public function setAverageGameRank(float $averageGameRank): static
    {
        $this->averageGameRank = $averageGameRank;
        return $this;
    }

    public function getAverageGameRank(): ?float
    {
        return $this->averageGameRank;
    }
}
