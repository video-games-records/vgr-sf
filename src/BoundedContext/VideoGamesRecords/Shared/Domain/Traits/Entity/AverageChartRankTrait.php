<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;

trait AverageChartRankTrait
{
    #[ORM\Column(nullable: true)]
    private ?float $averageChartRank = null;

    public function setAverageChartRank(float $averageChartRank): static
    {
        $this->averageChartRank = $averageChartRank;
        return $this;
    }

    public function getAverageChartRank(): ?float
    {
        return $this->averageChartRank;
    }
}
