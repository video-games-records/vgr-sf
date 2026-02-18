<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Application\DTO\Video;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\ApiPlatform\Video\VideoCollectionDataProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\ApiPlatform\Video\VideoDataProvider;

#[ApiResource(
    shortName: 'Video',
    operations: [
        new Get(
            uriTemplate: '/videos/{id}',
            requirements: ['id' => '\d+'],
            provider: VideoDataProvider::class,
            openapi: new Model\Operation(
                tags: ['Video'],
            )
        ),
        new GetCollection(
            uriTemplate: '/videos',
            provider: VideoCollectionDataProvider::class,
            paginationEnabled: true,
            paginationItemsPerPage: 30,
            openapi: new Model\Operation(
                tags: ['Video'],
            )
        ),
    ]
)]
class VideoDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly string $externalId,
        public readonly string $url,
        public readonly int $nbComment,
        public readonly string $slug,
        /** @var array{id: int, slug: string, name: string}|null */
        public readonly ?array $game,
        public readonly ?\DateTimeInterface $createdAt,
        /** @var array{id: int, pseudo: string, slug: string} */
        public readonly array $player,
        public readonly int $viewCount,
        public readonly int $likeCount,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?string $thumbnail,
    ) {
    }
}
