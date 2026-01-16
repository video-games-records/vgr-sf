<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\Message;

readonly class UpdateTeamChartRank
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
        return 'UpdateTeamChartRank' . $this->chartId;
    }
}
