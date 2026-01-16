<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Event\Admin;

use Symfony\Contracts\EventDispatcher\Event;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;

class AdminPlayerChartUpdated extends Event
{
    protected PlayerChart $playerChart;

    public function __construct(PlayerChart $playerChart)
    {
        $this->playerChart = $playerChart;
    }

    public function getPlayerChart(): PlayerChart
    {
        return $this->playerChart;
    }
}
