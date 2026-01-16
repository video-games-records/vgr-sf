<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player;

readonly class UpdatePlayerChartRank
{
    public function __construct(
        private int $chartId,
    ) {
    }

    public function getChartId(): int
    {
        return $this->chartId;
    }

    public function getUniqueIdentifier(): string
    {
        return 'UpdatePlayerChartRank' . $this->chartId;
    }
}
