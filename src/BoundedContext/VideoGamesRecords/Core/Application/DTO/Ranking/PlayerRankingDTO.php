<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Ranking;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\NotExposed;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Game\GamePlayerRankingDataProvider;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Serie\SeriePlayerRankingDataProvider;

#[ApiResource(
    shortName: 'PlayerRanking',
    operations: [
        new NotExposed(),
    ]
)]
#[ApiResource(
    shortName: 'PlayerRanking',
    uriTemplate: '/games/{id}/player-ranking',
    operations: [
        new GetCollection(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: GamePlayerRankingDataProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Game'],
            )
        ),
    ]
)]
#[ApiResource(
    shortName: 'PlayerRanking',
    uriTemplate: '/series/{id}/player-ranking',
    operations: [
        new GetCollection(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: SeriePlayerRankingDataProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Serie'],
            )
        ),
    ]
)]
class PlayerRankingDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $rank,
        public readonly int $pointChart,
        public readonly int $nbChart,
        public readonly int $nbChartProven,
        public readonly int $platinum,
        public readonly int $gold,
        public readonly int $silver,
        public readonly int $bronze,
        /** @var array{id: int, pseudo: string, slug: string, country: array{id: int, name: string, codeIso2: string}|null, team: array{id: int, name: string, slug: string}|null} */
        public readonly array $player,
    ) {
    }
}
