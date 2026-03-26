<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\PlayerGame\PlayerGameDTO;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGame;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PlayerGameMapper
{
    public function __construct(
        #[Autowire(env: 'STORAGE_PUBLIC_URL')]
        private readonly string $storagePublicUrl,
    ) {
    }

    public function toDTO(PlayerGame $playerGame): PlayerGameDTO
    {
        $game = $playerGame->getGame();

        return new PlayerGameDTO(
            gameId: (int) $game->getId(),
            gameName: $game->getName(),
            gamePicture: $this->storagePublicUrl . '/game/' . ($game->getPicture() ?: 'default.png'),
            gameSlug: $game->getSlug(),
            lastUpdate: $playerGame->getLastUpdate(),
            pointChart: $playerGame->getPointChart(),
            pointGame: $playerGame->getPointGame(),
            rankPointChart: $playerGame->getRankPointChart(),
            chartRank0: $playerGame->getChartRank0(),
            chartRank1: $playerGame->getChartRank1(),
            chartRank2: $playerGame->getChartRank2(),
            chartRank3: $playerGame->getChartRank3(),
            nbChart: $playerGame->getNbChart(),
            nbChartProven: $playerGame->getNbChartProven(),
        );
    }
}
