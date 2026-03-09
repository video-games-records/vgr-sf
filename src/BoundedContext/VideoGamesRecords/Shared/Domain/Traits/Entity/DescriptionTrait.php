<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;

trait DescriptionTrait
{
    #[ORM\Column(nullable: true, type: 'text')]
    private ?string $description = null;

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
