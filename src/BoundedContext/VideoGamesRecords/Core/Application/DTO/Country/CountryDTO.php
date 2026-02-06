<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Country;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Country\CountryDataProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/countries/{id}',
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Country'],
                summary: 'Get a country',
                description: 'Retrieves a specific country by ID'
            ),
            provider: CountryDataProvider::class
        )
    ],
    paginationEnabled: false
)]
readonly class CountryDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $iso2,
        public string $iso3,
        public string $slug
    ) {
    }
}
