<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Chart\ChartFormDataProvider;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Game\GameFormDataProvider;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Group\GroupFormDataProvider;
use App\BoundedContext\VideoGamesRecords\Core\Presentation\Api\Controller\PlayerChart\BulkUpsert;

#[ApiResource(
    uriTemplate: '/charts/{id}/form-data',
    operations: [
        new GetCollection(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: ChartFormDataProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Chart'],
            )
        ),
    ]
)]
#[ApiResource(
    uriTemplate: '/games/{id}/form-data',
    operations: [
        new GetCollection(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: GameFormDataProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Game'],
            )
        ),
    ]
)]
#[ApiResource(
    uriTemplate: '/groups/{id}/form-data',
    operations: [
        new GetCollection(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: GroupFormDataProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Group'],
            )
        ),
    ]
)]
#[ApiResource(
    uriTemplate: '/player-charts/bulk-upsert',
    operations: [
        new Post(
            controller: BulkUpsert::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            security: "is_granted('ROLE_PLAYER')",
            openapi: new Model\Operation(
                tags: ['PlayerChart'],
                summary: 'Bulk create or update player scores',
            )
        ),
    ]
)]
class ChartFormDataDTO
{
    /**
     * @param array<ChartLibDTO> $libs
     * @param array{id: int, name: string, slug: string}|null $group
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly bool $isProofVideoOnly,
        public readonly array $libs,
        public readonly PlayerChartFormDTO $playerChart,
        public readonly ?array $group = null,
    ) {
    }
}
