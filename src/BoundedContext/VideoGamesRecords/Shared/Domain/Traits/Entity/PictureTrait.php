<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;

trait PictureTrait
{
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $picture;

    public function setPicture(?string $picture = null): void
    {
        $this->picture = $picture;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }
}
