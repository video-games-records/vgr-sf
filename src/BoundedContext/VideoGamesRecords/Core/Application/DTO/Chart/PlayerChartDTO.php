<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\NotExposed;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Chart\ChartPlayerRankingDataProvider;

#[ApiResource(
    shortName: 'PlayerChart',
    operations: [
        new NotExposed(),
    ]
)]
#[ApiResource(
    shortName: 'PlayerChart',
    uriTemplate: '/charts/{id}/player-ranking',
    operations: [
        new GetCollection(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: ChartPlayerRankingDataProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Chart'],
            )
        ),
    ]
)]
class PlayerChartDTO
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
        public readonly array $values
    ) {
    }
}
