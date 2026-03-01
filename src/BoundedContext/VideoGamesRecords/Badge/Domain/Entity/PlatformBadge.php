<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;

#[ORM\Entity]
class PlatformBadge extends Badge
{
    #[ORM\OneToOne(targetEntity: Platform::class, mappedBy: 'badge')]
    private ?Platform $platform = null;

    public function __construct()
    {
        $this->setType(BadgeType::PLATFORM);
    }

    public function getPlatform(): ?Platform
    {
        return $this->platform;
    }
}
