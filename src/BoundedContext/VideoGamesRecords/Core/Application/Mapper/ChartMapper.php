<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart\ChartDTO;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;

class ChartMapper
{
    public function toDTO(Chart $chart): ChartDTO
    {
        $group = [
            'id' => (int) $chart->getGroup()->getId(),
            'name' => $chart->getGroup()->getName() ?? '',
            'slug' => $chart->getGroup()->getSlug()
        ];

        return new ChartDTO(
            id: (int) $chart->getId(),
            name: $chart->getName() ?? '',
            nbPost: $chart->getNbPost(),
            isDlc: $chart->getIsDlc(),
            slug: $chart->getSlug(),
            group: $group
        );
    }
}
