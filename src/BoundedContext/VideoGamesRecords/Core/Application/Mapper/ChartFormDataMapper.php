<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart\ChartFormDataDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart\ChartLibDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart\ChartTypeDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart\PlayerChartFormDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart\PlayerChartLibFormDTO;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;

class ChartFormDataMapper
{
    public function toDTO(Chart $chart, ?PlayerChart $playerChart, bool $includeGroup = false): ChartFormDataDTO
    {
        $libs = [];
        foreach ($chart->getLibs() as $lib) {
            $libs[] = $this->toChartLibDTO($lib);
        }

        $group = null;
        if ($includeGroup) {
            $group = [
                'id' => (int) $chart->getGroup()->getId(),
                'name' => $chart->getGroup()->getName() ?? '',
                'slug' => $chart->getGroup()->getSlug(),
            ];
        }

        return new ChartFormDataDTO(
            id: (int) $chart->getId(),
            name: $chart->getName() ?? '',
            slug: $chart->getSlug(),
            isProofVideoOnly: $chart->getIsProofVideoOnly(),
            libs: $libs,
            playerChart: $playerChart !== null
                ? $this->toPlayerChartFormDTO($playerChart)
                : $this->toEmptyPlayerChartFormDTO($chart),
            group: $group,
        );
    }

    private function toChartLibDTO(ChartLib $lib): ChartLibDTO
    {
        $type = $lib->getType();

        return new ChartLibDTO(
            id: (int) $lib->getId(),
            name: $lib->getName(),
            type: new ChartTypeDTO(
                id: (int) $type->getId(),
                mask: $type->getMask(),
                parseMask: $type->getParseMask(),
            ),
        );
    }

    private function toPlayerChartFormDTO(PlayerChart $playerChart): PlayerChartFormDTO
    {
        $platform = null;
        if ($playerChart->getPlatform() !== null) {
            $platform = [
                'id' => (int) $playerChart->getPlatform()->getId(),
                'name' => $playerChart->getPlatform()->getName(),
                'slug' => $playerChart->getPlatform()->getSlug(),
            ];
        }

        $libs = [];
        foreach ($playerChart->getLibs() as $playerChartLib) {
            $libs[] = new PlayerChartLibFormDTO(
                id: (int) $playerChartLib->getId(),
                libChartId: (int) $playerChartLib->getLibChart()->getId(),
                value: $playerChartLib->getValue(),
                parseValue: $playerChartLib->getParseValue(),
            );
        }

        return new PlayerChartFormDTO(
            id: (int) $playerChart->getId(),
            rank: $playerChart->getRank(),
            pointChart: $playerChart->getPointChart(),
            status: $playerChart->getStatus()->value,
            platform: $platform,
            lastUpdate: $playerChart->getLastUpdate(),
            libs: $libs,
        );
    }

    private function toEmptyPlayerChartFormDTO(Chart $chart): PlayerChartFormDTO
    {
        $libs = [];
        foreach ($chart->getLibs() as $lib) {
            $parseMask = $lib->getType()->getParseMask();
            $emptyParseValue = [];
            foreach ($parseMask as $part) {
                $emptyParseValue[] = ['value' => ''];
            }

            $libs[] = new PlayerChartLibFormDTO(
                id: -1,
                libChartId: (int) $lib->getId(),
                value: null,
                parseValue: $emptyParseValue,
            );
        }

        return new PlayerChartFormDTO(
            id: -1,
            rank: null,
            pointChart: 0,
            status: 'none',
            platform: null,
            lastUpdate: null,
            libs: $libs,
        );
    }
}
