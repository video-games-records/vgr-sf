<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Game;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Game\GameDataProvider;

#[ApiResource(
    uriTemplate: '/games/{id}',
    operations: [
        new Get(
            provider: GameDataProvider::class,
            openapi: new Model\Operation(
                tags: ['Game'],
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
