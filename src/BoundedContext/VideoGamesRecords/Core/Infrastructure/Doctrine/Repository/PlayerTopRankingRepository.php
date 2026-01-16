<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository;

use Doctrine\Persistence\ManagerRegistry;
use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerTopRanking;

class PlayerTopRankingRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerTopRanking::class);
    }


    /**
     * Delete old rankings to keep only recent data
     *
     * @param string $periodType
     * @param string $beforePeriodValue
     * @return int Number of deleted records
     */
    public function deleteOldRankings(string $periodType, string $beforePeriodValue): int
    {
        return $this->createQueryBuilder('ptr')
            ->delete()
            ->andWhere('ptr.periodType = :periodType')
            ->andWhere('ptr.periodValue < :beforePeriodValue')
            ->setParameter('periodType', $periodType)
            ->setParameter('beforePeriodValue', $beforePeriodValue)
            ->getQuery()
            ->execute();
    }
}
