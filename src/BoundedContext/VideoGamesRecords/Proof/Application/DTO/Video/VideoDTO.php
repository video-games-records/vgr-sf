<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Application\DTO\Video;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\ApiPlatform\Video\VideoCollectionDataProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\ApiPlatform\Video\VideoDataProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\ApiPlatform\Video\VideoRelatedDataProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Presentation\Api\Controller\Video\CreateVideo;

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
        new GetCollection(
            uriTemplate: '/videos/{id}/related-videos',
            requirements: ['id' => '\d+'],
            provider: VideoRelatedDataProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Video'],
                summary: 'Get related videos',
                description: 'Returns a list of recommended videos related to the given video, ranked by relevance.',
            )
        ),
        new Post(
            uriTemplate: '/videos',
            controller: CreateVideo::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            security: "is_granted('ROLE_PLAYER')",
            openapi: new Model\Operation(
                tags: ['Video'],
                summary: 'Create a video',
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
