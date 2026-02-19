<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Application\DTO\Player;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\ApiPlatform\Player\PlayerChannelDataProvider;

#[ApiResource(
    shortName: 'PlayerChannel',
    operations: [
        new GetCollection(
            uriTemplate: '/players/channels',
            provider: PlayerChannelDataProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Player'],
                summary: 'Get players with video channels',
                description: 'Returns players who have at least 5 videos, ordered alphabetically.',
            )
        ),
    ]
)]
class PlayerChannelDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $pseudo,
        public readonly string $slug,
        public readonly int $nbVideo,
    ) {
    }
}
