<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Player;

class PlayerStatsDTO
{
    public function __construct(
        public readonly int $pointGame,
        public readonly int $pointChart,
        public readonly int $pointBadge,
        public readonly int $nbGame,
        public readonly int $nbChart,
        public readonly int $nbVideo,
        public readonly int $nbMasterBadge,
        public readonly int $nbChartProven,
        public readonly int $nbChartMax,
        public readonly int $chartRank0,
        public readonly int $chartRank1,
        public readonly int $chartRank2,
        public readonly int $chartRank3,
        public readonly int $chartRank4,
        public readonly int $chartRank5,
        public readonly int $gameRank0,
        public readonly int $gameRank1,
        public readonly int $gameRank2,
        public readonly int $gameRank3,
        public readonly int $rankCup,
        public readonly int $rankMedal,
        public readonly int $rankBadge,
        public readonly int $rankPointChart,
        public readonly int $rankPointGame,
        public readonly int $rankCountry,
        public readonly int $rankProof,
        public readonly ?float $averageChartRank,
        public readonly ?float $averageGameRank,
    ) {
    }
}
