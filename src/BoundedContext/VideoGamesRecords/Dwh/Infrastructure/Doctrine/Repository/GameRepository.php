<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Infrastructure\Doctrine\Repository;

use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Game;

/**
 * @extends DefaultRepository<Game>
 */
class GameRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /** @return array<int, array<string, mixed>> */
    public function getTop(DateTime $begin, DateTime $end, int $limit = 20): array
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT g.id,
                   SUM(g.nbPostDay) as nb
            FROM App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Game g
            WHERE g.date BETWEEN :begin AND :end
            GROUP BY g.id
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
    public function getTotalNbGame(DateTime $begin, DateTime $end): mixed
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(DISTINCT(g.id)) as nb
            FROM App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Game g
            WHERE g.date BETWEEN :begin AND :end
            AND g.nbPostDay > 0");

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
            SELECT SUM(g.nbPostDay) as nb
            FROM App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Game g
            WHERE g.date BETWEEN :begin AND :end");

        $query->setParameter('begin', $begin);
        $query->setParameter('end', $end);

        return $query->getSingleScalarResult();
    }
}
