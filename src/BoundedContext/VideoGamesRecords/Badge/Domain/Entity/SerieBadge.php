<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;

#[ORM\Entity]
class SerieBadge extends Badge
{
    #[ORM\OneToOne(targetEntity: Serie::class, mappedBy: 'badge')]
    private ?Serie $serie = null;

    public function __construct()
    {
        $this->setType(BadgeType::SERIE);
    }

    public function getSerie(): ?Serie
    {
        return $this->serie;
    }
}
