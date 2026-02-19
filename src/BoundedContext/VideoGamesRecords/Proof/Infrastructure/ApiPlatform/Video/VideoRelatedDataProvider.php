<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\ApiPlatform\Video;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Proof\Application\DTO\Video\VideoDTO;
use App\BoundedContext\VideoGamesRecords\Proof\Application\Mapper\VideoMapper;
use App\BoundedContext\VideoGamesRecords\Proof\Application\Service\VideoRecommendationService;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository\VideoRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<VideoDTO> */
class VideoRelatedDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly VideoRepository $videoRepository,
        private readonly VideoMapper $videoMapper,
        private readonly VideoRecommendationService $videoRecommendationService,
    ) {
    }

    /**
     * @return array<VideoDTO>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $video = $this->videoRepository->find($uriVariables['id']);

        if (!$video) {
            throw new NotFoundHttpException('Video not found');
        }

        $relatedVideos = $this->videoRecommendationService->getRelatedVideos($video);

        return array_map(
            fn ($video) => $this->videoMapper->toDTO($video),
            $relatedVideos
        );
    }
}
