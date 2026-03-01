<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;

#[ORM\Entity]
class CountryBadge extends Badge
{
    #[ORM\OneToOne(targetEntity: Country::class, mappedBy: 'badge')]
    private ?Country $country = null;

    public function __construct()
    {
        $this->setType(BadgeType::VGR_SPECIAL_COUNTRY);
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }
}
