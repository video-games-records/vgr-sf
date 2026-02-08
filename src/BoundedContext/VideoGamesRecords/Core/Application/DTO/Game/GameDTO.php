<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Game;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Game\GameDataProvider;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Serie\SerieGameDataProvider;

#[ApiResource(
    uriTemplate: '/games/{id}',
    operations: [
        new Get(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: GameDataProvider::class,
            openapi: new Model\Operation(
                tags: ['Game'],
            )
        ),
    ]
)]
#[ApiResource(
    uriTemplate: '/series/{id}/games',
    operations: [
        new GetCollection(
            uriVariables: ['id'],
            requirements: ['id' => '\d+'],
            provider: SerieGameDataProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Serie'],
            )
        ),
    ]
)]
class GameDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $picture,
        public readonly string $status,
        public readonly ?\DateTimeInterface $publishedAt,
        public readonly bool $isRank,
        public readonly int $nbChart,
        public readonly int $nbPost,
        public readonly int $nbPlayer,
        public readonly int $nbTeam,
        public readonly ?\DateTimeInterface $releaseDate,
        public readonly string $slug,
        public readonly ?string $downloadUrl,
        public readonly ?\DateTimeInterface $lastUpdate,
        /** @var array{id: int, name: string, slug: string}|null */
        public readonly ?array $serie,
        /** @var array<array{id: int, name: string, slug: string}> */
        public readonly array $platforms,
        /** @var array<array{id: int, name: string, slug: string}> */
        public readonly array $genres
    ) {
    }
}
