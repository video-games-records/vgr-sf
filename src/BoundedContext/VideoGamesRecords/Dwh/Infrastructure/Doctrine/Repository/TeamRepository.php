<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Infrastructure\Doctrine\Repository;

use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Team;

/**
 * @extends DefaultRepository<Team>
 */
class TeamRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }
}
