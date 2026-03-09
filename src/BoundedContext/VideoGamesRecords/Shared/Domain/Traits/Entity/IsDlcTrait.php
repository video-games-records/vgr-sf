<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;

trait IsDlcTrait
{
    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $isDlc = false;

    public function setIsDlc(bool $isDlc): static
    {
        $this->isDlc = $isDlc;
        return $this;
    }

    public function getIsDlc(): bool
    {
        return $this->isDlc;
    }
}
