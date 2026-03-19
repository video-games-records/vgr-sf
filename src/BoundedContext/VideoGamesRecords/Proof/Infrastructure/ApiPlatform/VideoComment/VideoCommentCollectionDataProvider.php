<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\ApiPlatform\VideoComment;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Proof\Application\DTO\VideoComment\VideoCommentDTO;
use App\BoundedContext\VideoGamesRecords\Proof\Application\Mapper\VideoCommentMapper;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository\VideoCommentRepository;

/** @implements ProviderInterface<VideoCommentDTO> */
class VideoCommentCollectionDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly VideoCommentRepository $videoCommentRepository,
        private readonly VideoCommentMapper $videoCommentMapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $videoId = (int) $uriVariables['videoId'];
        $page = (int) ($context['filters']['page'] ?? 1);
        $itemsPerPage = (int) ($context['filters']['itemsPerPage'] ?? $operation->getPaginationItemsPerPage() ?? 20);
        $offset = ($page - 1) * $itemsPerPage;

        $comments = $this->videoCommentRepository->findByVideoPaginated($videoId, $offset, $itemsPerPage);
        $totalItems = $this->videoCommentRepository->countByVideo($videoId);

        $dtos = array_map(
            fn ($comment) => $this->videoCommentMapper->toDTO($comment),
            $comments
        );

        return new TraversablePaginator(
            new \ArrayIterator($dtos),
            $page,
            $itemsPerPage,
            $totalItems,
        );
    }
}