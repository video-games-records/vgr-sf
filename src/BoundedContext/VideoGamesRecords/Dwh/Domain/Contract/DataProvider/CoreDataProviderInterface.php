<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Domain\Contract\DataProvider;

use DateTime;

interface CoreDataProviderInterface
{
    /** @return array<mixed> */
    public function getData(): array;

    /** @return array<int, int> */
    public function getNbPostDay(DateTime $date1, DateTime $date2): array;
}
