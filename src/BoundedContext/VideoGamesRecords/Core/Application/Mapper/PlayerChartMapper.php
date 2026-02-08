<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart\PlayerChartDTO;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\ScoreTools;

class PlayerChartMapper
{
    public function toDTO(PlayerChart $playerChart): PlayerChartDTO
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

        return new PlayerChartDTO(
            id: (int) $playerChart->getId(),
            rank: $playerChart->getRank(),
            pointChart: $playerChart->getPointChart(),
            player: $player,
            platform: $platform,
            values: $values
        );
    }
}
