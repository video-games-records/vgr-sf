<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart;

class PlayerChartLibFormDTO
{
    /**
     * @param array<array{value: string}> $parseValue
     */
    public function __construct(
        public readonly int $id,
        public readonly int $libChartId,
        public readonly ?string $value,
        public readonly array $parseValue,
    ) {
    }
}
