<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart;

class ChartLibDTO
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $name,
        public readonly ChartTypeDTO $type,
    ) {
    }
}
