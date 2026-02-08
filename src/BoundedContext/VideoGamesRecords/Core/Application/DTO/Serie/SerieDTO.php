<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Serie;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Serie\SerieDataProvider;

#[ApiResource(
    uriTemplate: '/series/{id}',
    operations: [
        new Get(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: SerieDataProvider::class,
            openapi: new Model\Operation(
                tags: ['Serie'],
            )
        ),
    ]
)]
class SerieDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $picture,
        public readonly string $status,
        public readonly int $nbChart,
        public readonly int $nbGame,
        public readonly int $nbPlayer,
        public readonly int $nbTeam,
        public readonly string $slug
    ) {
    }
}
