<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Serie;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Serie\LatestScoreDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\LatestScoreMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\SerieRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<LatestScoreDTO> */
class SerieLatestScoresDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly SerieRepository $serieRepository,
        private readonly PlayerChartRepository $playerChartRepository,
        private readonly LatestScoreMapper $latestScoreMapper,
    ) {
    }

    /**
     * @return array<LatestScoreDTO>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $id = $uriVariables['id'] ?? null;

        if ($id === null) {
            return [];
        }

        $serie = $this->serieRepository->find((int) $id);

        if ($serie === null) {
            throw new NotFoundHttpException('Serie not found');
        }

        $playerCharts = $this->playerChartRepository->findLatestBySerie($serie);

        return array_map(
            fn ($playerChart) => $this->latestScoreMapper->toDTO($playerChart),
            $playerCharts
        );
    }
}
