<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Serie;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\NotExposed;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Serie\SerieLatestScoresDataProvider;

#[ApiResource(
    shortName: 'LatestScore',
    operations: [
        new NotExposed(),
    ]
)]
#[ApiResource(
    shortName: 'LatestScore',
    uriTemplate: '/series/{id}/latest-scores',
    operations: [
        new GetCollection(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: SerieLatestScoresDataProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Serie'],
            )
        ),
    ]
)]
class LatestScoreDTO
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $rank,
        public readonly int $pointChart,
        /** @var array{id: int, pseudo: string, slug: string} */
        public readonly array $player,
        /** @var array{id: int, name: string, slug: string}|null */
        public readonly ?array $platform,
        /** @var array<array{libChartName: ?string, value: string}> */
        public readonly array $values,
        /** @var array{id: int, name: string|null, slug: string, group: array{id: int, name: string|null, slug: string, game: array{id: int, name: string, slug: string}}} */
        public readonly array $chart,
        public readonly ?\DateTime $lastUpdate,
    ) {
    }
}
