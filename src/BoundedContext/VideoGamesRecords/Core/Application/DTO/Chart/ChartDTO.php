<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Chart\ChartDataProvider;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Group\GroupChartDataProvider;

#[ApiResource(
    uriTemplate: '/charts/{id}',
    operations: [
        new Get(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: ChartDataProvider::class,
            openapi: new Model\Operation(
                tags: ['Chart'],
            )
        ),
    ]
)]
#[ApiResource(
    uriTemplate: '/groups/{id}/charts',
    operations: [
        new GetCollection(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: GroupChartDataProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Group'],
            )
        ),
    ]
)]
class ChartDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $nbPost,
        public readonly bool $isDlc,
        public readonly string $slug,
        /** @var array{id: int, name: string, slug: string} */
        public readonly array $group
    ) {
    }
}
