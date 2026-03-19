<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Application\DTO\VideoComment;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Proof\Application\DTO\Video\VideoDTO;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\ApiPlatform\VideoComment\VideoCommentCollectionDataProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Presentation\Api\Controller\VideoComment\CreateVideoComment;

#[ApiResource(
    shortName: 'VideoComment',
    operations: [
        new GetCollection(
            uriTemplate: '/videos/{videoId}/comments',
            requirements: ['videoId' => '\d+'],
            uriVariables: [
                'videoId' => new Link(
                    fromClass: VideoDTO::class,
                    identifiers: ['id'],
                ),
            ],
            provider: VideoCommentCollectionDataProvider::class,
            paginationEnabled: true,
            paginationItemsPerPage: 20,
            openapi: new Model\Operation(
                tags: ['VideoComment'],
                summary: 'Get comments for a video',
                description: 'Returns paginated comments for a specific video.',
            )
        ),
        new Post(
            uriTemplate: '/video_comments',
            controller: CreateVideoComment::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            security: "is_granted('ROLE_USER')",
            openapi: new Model\Operation(
                tags: ['VideoComment'],
                summary: 'Create a comment',
                description: 'Creates a new comment on a video.',
            )
        ),
    ]
)]
class VideoCommentDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $content,
        public readonly ?\DateTimeInterface $createdAt,
        /** @var array{id: int, pseudo: string, slug: string} */
        public readonly array $player,
    ) {
    }
}
