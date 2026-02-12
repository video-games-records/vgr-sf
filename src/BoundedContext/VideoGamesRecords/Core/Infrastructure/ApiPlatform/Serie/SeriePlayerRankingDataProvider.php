<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Serie;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DataProvider\Ranking\PlayerSerieRankingProvider;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Ranking\PlayerRankingDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\PlayerRankingMapper;

/** @implements ProviderInterface<PlayerRankingDTO> */
class SeriePlayerRankingDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly PlayerSerieRankingProvider $playerSerieRankingProvider,
        private readonly PlayerRankingMapper $playerRankingMapper
    ) {
    }

    /**
     * @return array<PlayerRankingDTO>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $id = $uriVariables['id'] ?? null;

        if ($id === null) {
            return [];
        }

        $playerSeries = $this->playerSerieRankingProvider->getRankingPoints((int) $id, ['maxRank' => 100]);

        return array_map(
            fn ($playerSerie) => $this->playerRankingMapper->fromPlayerSerie($playerSerie),
            $playerSeries
        );
    }
}
