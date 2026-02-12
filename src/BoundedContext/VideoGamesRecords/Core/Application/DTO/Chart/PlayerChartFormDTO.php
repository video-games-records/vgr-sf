<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart;

use DateTime;

class PlayerChartFormDTO
{
    /**
     * @param array{id: int, name: string, slug: string}|null $platform
     * @param array<PlayerChartLibFormDTO> $libs
     */
    public function __construct(
        public readonly int $id,
        public readonly ?int $rank,
        public readonly int $pointChart,
        public readonly string $status,
        public readonly ?array $platform,
        public readonly ?DateTime $lastUpdate,
        public readonly array $libs,
    ) {
    }
}
