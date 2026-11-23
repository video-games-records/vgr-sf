<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository;

use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Team\Application\DTO\TeamListFilter;
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
     * @return Team[]
     */
    public function findForList(TeamListFilter $filter, string $sortBy = 'pointGame', string $order = 'DESC', int $limit = 100, int $offset = 0): array
    {
        if (!in_array($sortBy, self::VALID_SORT_FIELDS, true)) {
            $sortBy = 'pointGame';
        }

        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $qb = $this->buildListQuery($filter)
            ->orderBy('team.' . $sortBy, $order)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function countForList(TeamListFilter $filter): int
    {
        $qb = $this->buildListQuery($filter)
            ->select('COUNT(team.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function buildListQuery(TeamListFilter $filter): QueryBuilder
    {
        $qb = $this->createQueryBuilder('team')
            ->where('team.nbPlayer > 0');

        if ($filter->search !== null && $filter->search !== '') {
            $qb->andWhere('LOWER(team.libTeam) LIKE LOWER(:search) OR LOWER(team.tag) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $filter->search . '%');
        }

        if ($filter->status !== null) {
            $qb->andWhere('team.status = :status')
                ->setParameter('status', $filter->status);
        }

        if ($filter->minPlayers !== null) {
            $qb->andWhere('team.nbPlayer >= :minPlayers')
                ->setParameter('minPlayers', $filter->minPlayers);
        }

        if ($filter->pointsMin !== null) {
            $qb->andWhere('team.pointGame >= :pointsMin')
                ->setParameter('pointsMin', $filter->pointsMin);
        }

        if ($filter->pointsMax !== null) {
            $qb->andWhere('team.pointGame <= :pointsMax')
                ->setParameter('pointsMax', $filter->pointsMax);
        }

        if ($filter->hasBadges) {
            $qb->andWhere('team.nbMasterBadge > 0');
        }

        return $qb;
    }

    /**
     * @return Team[]
     */
    public function autocomplete(string $q): array
    {
        $qb = $this->createQueryBuilder('team')
            ->where('team.libTeam LIKE :q')
            ->andWhere('team.nbPlayer > 0')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('team.libTeam', 'ASC');

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
