<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;

trait NbTeamTrait
{
    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $nbTeam = 0;

    public function setNbTeam(int $nbTeam): static
    {
        $this->nbTeam = $nbTeam;
        return $this;
    }

    public function getNbTeam(): int
    {
        return $this->nbTeam;
    }
}
