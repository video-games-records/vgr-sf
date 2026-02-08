<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Country\CountryDTO;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;

class CountryMapper
{
    public function toDTO(Country $country): CountryDTO
    {
        return new CountryDTO(
            id: (int) $country->getId(),
            name: $country->getName() ?? $country->getDefaultName(),
            iso2: $country->getCodeIso2(),
            iso3: $country->getCodeIso3(),
            slug: $country->getSlug()
        );
    }

    /**
     * @param Country[] $countries
     * @return CountryDTO[]
     */
    public function toDTOCollection(array $countries): array
    {
        return array_map(
            fn(Country $country) => $this->toDTO($country),
            $countries
        );
    }
}
