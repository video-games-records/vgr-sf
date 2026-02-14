<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\PlayerSerie\PlayerSerieDTO;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerSerie;

class PlayerSerieMapper
{
    public function toDTO(PlayerSerie $playerSerie): PlayerSerieDTO
    {
        return new PlayerSerieDTO(
            platinum: $playerSerie->getChartRank0(),
            gold: $playerSerie->getChartRank1(),
            silver: $playerSerie->getChartRank2(),
            bronze: $playerSerie->getChartRank3(),
            rank: $playerSerie->getRankPointChart(),
            pointChart: $playerSerie->getPointChart(),
            nbChart: $playerSerie->getNbChart(),
            nbChartProven: $playerSerie->getNbChartProven(),
            nbGame: $playerSerie->getNbGame(),
        );
    }
}
