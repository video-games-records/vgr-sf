<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Game;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DataProvider\Ranking\PlayerGameRankingProvider;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Ranking\PlayerRankingDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\PlayerRankingMapper;

/** @implements ProviderInterface<PlayerRankingDTO> */
class GamePlayerRankingDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly PlayerGameRankingProvider $playerGameRankingProvider,
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

        $playerGames = $this->playerGameRankingProvider->getRankingPoints((int) $id, ['maxRank' => 100]);

        return array_map(
            fn ($playerGame) => $this->playerRankingMapper->fromPlayerGame($playerGame),
            $playerGames
        );
    }
}
