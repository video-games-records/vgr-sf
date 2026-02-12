<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Serie\LatestScoreDTO;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\ScoreTools;

class LatestScoreMapper
{
    public function toDTO(PlayerChart $playerChart): LatestScoreDTO
    {
        $player = [
            'id' => (int) $playerChart->getPlayer()->getId(),
            'pseudo' => $playerChart->getPlayer()->getPseudo(),
            'slug' => $playerChart->getPlayer()->getSlug(),
        ];

        $platform = null;
        if ($playerChart->getPlatform() !== null) {
            $platform = [
                'id' => (int) $playerChart->getPlatform()->getId(),
                'name' => $playerChart->getPlatform()->getName(),
                'slug' => $playerChart->getPlatform()->getSlug(),
            ];
        }

        $values = [];
        foreach ($playerChart->getLibs() as $lib) {
            $values[] = [
                'libChartName' => $lib->getLibChart()->getName(),
                'value' => ScoreTools::formatScore(
                    $lib->getValue(),
                    $lib->getLibChart()->getType()->getMask()
                ),
            ];
        }

        $chartEntity = $playerChart->getChart();
        $groupEntity = $chartEntity->getGroup();
        $gameEntity = $groupEntity->getGame();

        $chart = [
            'id' => (int) $chartEntity->getId(),
            'name' => $chartEntity->getName(),
            'slug' => $chartEntity->getSlug(),
            'group' => [
                'id' => (int) $groupEntity->getId(),
                'name' => $groupEntity->getName(),
                'slug' => $groupEntity->getSlug(),
                'game' => [
                    'id' => (int) $gameEntity->getId(),
                    'name' => $gameEntity->getName(),
                    'slug' => $gameEntity->getSlug(),
                ],
            ],
        ];

        return new LatestScoreDTO(
            id: (int) $playerChart->getId(),
            rank: $playerChart->getRank(),
            pointChart: $playerChart->getPointChart(),
            player: $player,
            platform: $platform,
            values: $values,
            chart: $chart,
            lastUpdate: $playerChart->getLastUpdate(),
        );
    }
}
