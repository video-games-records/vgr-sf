<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository;

use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;

/**
 * @extends DefaultRepository<Team>
 */
class TeamRepository extends DefaultRepository
{
    private const array VALID_SORT_FIELDS = [
        'libTeam',
        'nbGame',
        'pointGame',
        'nbMasterBadge',
        'nbPlayer',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /**
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function getStats(): mixed
    {
        $qb = $this->createQueryBuilder('team')
            ->select('COUNT(team.id)');
        $qb->where('team.pointChart > 0');

        return $qb->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Team[]
     */
    public function findAllWithSort(string $sortBy = 'pointGame', string $order = 'DESC', int $limit = 100, int $offset = 0): array
    {
        if (!in_array($sortBy, self::VALID_SORT_FIELDS, true)) {
            $sortBy = 'pointGame';
        }

        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'DESC';
        }

        $qb = $this->createQueryBuilder('team')
            ->where('team.nbPlayer > 0')
            ->orderBy('team.' . $sortBy, $order)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function countActiveTeams(): int
    {
        $qb = $this->createQueryBuilder('team')
            ->select('COUNT(team.id)')
            ->where('team.nbPlayer > 0');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
