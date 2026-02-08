<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Chart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DataProvider\Ranking\PlayerChartRankingProvider;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart\PlayerChartDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\PlayerChartMapper;

/** @implements ProviderInterface<PlayerChartDTO> */
class ChartPlayerRankingDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly PlayerChartRankingProvider $playerChartRankingProvider,
        private readonly PlayerChartMapper $playerChartMapper
    ) {
    }

    /**
     * @return array<PlayerChartDTO>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $id = $uriVariables['id'] ?? null;

        if ($id === null) {
            return [];
        }

        $playerCharts = $this->playerChartRankingProvider->getRankingPoints((int) $id);

        return array_map(
            fn ($playerChart) => $this->playerChartMapper->toDTO($playerChart),
            $playerCharts
        );
    }
}
