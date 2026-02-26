<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Infrastructure\Doctrine\Repository;

use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Player;

/**
 * @extends DefaultRepository<Player>
 */
class PlayerRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    /** @return array<int, array<string, mixed>> */
    public function getTop(DateTime $begin, DateTime $end, int $limit = 20): array
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT p.id,
                   SUM(p.nbPostDay) as nb
            FROM App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Player p
            WHERE p.date BETWEEN :begin AND :end
            GROUP BY p.id
            HAVING nb > 0
            ORDER BY nb DESC");

        $query->setParameter('begin', $begin);
        $query->setParameter('end', $end);
        $query->setMaxResults($limit);

        return $query->getArrayResult();
    }

    /**
     * @param DateTime $begin
     * @param DateTime $end
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getTotalNbPlayer(DateTime $begin, DateTime $end): mixed
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(DISTINCT(p.id)) as nb
            FROM App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Player p
            WHERE p.date BETWEEN :begin AND :end
            AND p.nbPostDay > 0");

        $query->setParameter('begin', $begin);
        $query->setParameter('end', $end);

        return $query->getSingleScalarResult();
    }

    /**
     * @param DateTime $begin
     * @param DateTime $end
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getTotalNbPostDay(DateTime $begin, DateTime $end): mixed
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT SUM(p.nbPostDay) as nb
            FROM App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Player p
            WHERE p.date BETWEEN :begin AND :end");

        $query->setParameter('begin', $begin);
        $query->setParameter('end', $end);

        return $query->getSingleScalarResult();
    }
}
