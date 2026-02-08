<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Group;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Game\GameGroupDataProvider;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Group\GroupDataProvider;

#[ApiResource(
    uriTemplate: '/groups/{id}',
    operations: [
        new Get(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: GroupDataProvider::class,
            openapi: new Model\Operation(
                tags: ['Group'],
            )
        ),
    ]
)]
#[ApiResource(
    uriTemplate: '/games/{id}/groups',
    operations: [
        new GetCollection(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: GameGroupDataProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Game'],
            )
        ),
    ]
)]
class GroupDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $nbChart,
        public readonly int $nbPost,
        public readonly int $nbPlayer,
        public readonly bool $isRank,
        public readonly bool $isDlc,
        public readonly string $slug,
        /** @var array{id: int, name: string, slug: string} */
        public readonly array $game
    ) {
    }
}
