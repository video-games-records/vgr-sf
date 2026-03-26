<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\PlayerGame;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Player\PlayerGamesDataProvider;

#[ApiResource(
    uriTemplate: '/players/{id}/games',
    operations: [
        new GetCollection(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: PlayerGamesDataProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Player'],
                summary: 'Get player games ordered by last score',
                description: 'Retrieves all games played by a player, ordered by most recently posted score'
            )
        ),
    ]
)]
class PlayerGameDTO
{
    public function __construct(
        public readonly int $gameId,
        public readonly string $gameName,
        public readonly ?string $gamePicture,
        public readonly string $gameSlug,
        public readonly \DateTimeInterface $lastUpdate,
        public readonly int $pointChart,
        public readonly int $pointGame,
        public readonly int $rankPointChart,
        public readonly int $chartRank0,
        public readonly int $chartRank1,
        public readonly int $chartRank2,
        public readonly int $chartRank3,
        public readonly int $nbChart,
        public readonly int $nbChartProven,
    ) {
    }
}
