<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\PlayerSerie;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\PlayerSerie\PlayerSerieDataProvider;

#[ApiResource(
    uriTemplate: '/players/{playerId}/series/{serieId}',
    operations: [
        new Get(
            uriVariables: ['playerId', 'serieId'],
            requirements: ['playerId' => '\d+', 'serieId' => '\d+'],
            provider: PlayerSerieDataProvider::class,
            openapi: new Model\Operation(
                tags: ['PlayerSerie'],
                summary: 'Get player stats for a serie',
            )
        ),
    ]
)]
class PlayerSerieDTO
{
    public function __construct(
        public readonly int $platinum,
        public readonly int $gold,
        public readonly int $silver,
        public readonly int $bronze,
        public readonly int $rank,
        public readonly int $pointChart,
        public readonly int $nbChart,
        public readonly int $nbChartProven,
        public readonly int $nbGame,
    ) {
    }
}
