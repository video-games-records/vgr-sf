<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\ApiPlatform\Video;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Proof\Application\DTO\Video\VideoDTO;
use App\BoundedContext\VideoGamesRecords\Proof\Application\Mapper\VideoMapper;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository\VideoRepository;

/** @implements ProviderInterface<VideoDTO> */
class VideoCollectionDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly VideoRepository $videoRepository,
        private readonly VideoMapper $videoMapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $page = (int) ($context['filters']['page'] ?? 1);
        $itemsPerPage = (int) ($context['filters']['itemsPerPage'] ?? $operation->getPaginationItemsPerPage() ?? 30);
        $offset = ($page - 1) * $itemsPerPage;

        $videos = $this->videoRepository->findActiveVideosPaginated($offset, $itemsPerPage);
        $totalItems = $this->videoRepository->countActiveVideos();

        $dtos = array_map(
            fn ($video) => $this->videoMapper->toDTO($video),
            $videos
        );

        return new TraversablePaginator(
            new \ArrayIterator($dtos),
            $page,
            $itemsPerPage,
            $totalItems,
        );
    }
}
