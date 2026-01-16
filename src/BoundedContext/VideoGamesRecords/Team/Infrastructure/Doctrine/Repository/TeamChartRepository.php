<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository;

use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamChart;

class TeamChartRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamChart::class);
    }
}
