<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart;

class ChartTypeDTO
{
    /**
     * @param array<array{size: int, suffixe: string}> $parseMask
     */
    public function __construct(
        public readonly int $id,
        public readonly string $mask,
        public readonly array $parseMask,
    ) {
    }
}
