<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;

trait NbPostDayTrait
{
    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $nbPostDay = 0;

    public function getNbPostDay(): int
    {
        return $this->nbPostDay;
    }

    public function setNbPostDay(int $nbPostDay): void
    {
        $this->nbPostDay = $nbPostDay;
    }
}
