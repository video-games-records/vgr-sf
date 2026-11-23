<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Player\PlayerListFilter;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerStatusEnum;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Entity\PlatformConnection;

/**
 * @extends DefaultRepository<Player>
 */
class PlayerRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    /**
     * @param $q
     * @return mixed
     */
    public function autocomplete(string $q)
    {
        $query = $this->createQueryBuilder('p');

        $query
            ->where('p.pseudo LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('p.pseudo', 'ASC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @return array<Player>
     */
    public function findBySearch(string $search, int $limit = 30, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->where('LOWER(p.pseudo) LIKE LOWER(:search)')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('p.pseudo', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $user
     * @return Player
     * @throws NonUniqueResultException
     */
    public function getPlayerFromUser(User $user): ?Player
    {
        $qb = $this->createQueryBuilder('player')
            ->where('player.user_id = :userId')
            ->setParameter('userId', $user->getId())
            ->addSelect('team')->leftJoin('player.team', 'team');

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getStats(): mixed
    {
        $qb = $this->createQueryBuilder('player')
            ->select('COUNT(player.id), SUM(player.nbChart), SUM(player.nbChartProven)');
        $qb->where('player.nbChart > 0');

        return $qb->getQuery()
            ->getOneOrNullResult();
    }


    /**
     * @return list<array<string, mixed>>
     * @throws Exception
     */
    public function getProofStats(): array
    {
        $sql = <<<SQL
            SELECT
                p.id,
                p.pseudo,
                COUNT(pr.id) as nb,
                SUBSTRING(pr.checked_at, 1, 7) as month
            FROM vgr_player p
            INNER JOIN vgr_proof pr ON pr.responding_player_id = p.id
            WHERE pr.checked_at > '2020-01-01'
            GROUP BY p.id, SUBSTRING(pr.checked_at, 1, 7)
            ORDER BY month DESC
        SQL;

        return $this->getEntityManager()->getConnection()->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * @param string $sortBy
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return array<Player>
     */
    public function findAllWithSort(string $sortBy = 'pseudo', string $order = 'ASC', int $limit = 100, int $offset = 0): array
    {
        $validFields = ['pointGame', 'lastLogin', 'pseudo', 'createdAt', 'nbGame', 'nbChart'];
        if (!in_array($sortBy, $validFields)) {
            $sortBy = 'pseudo';
        }

        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.team', 't')
            ->addSelect('t')
            ->leftJoin('p.country', 'c')
            ->addSelect('c')
            ->where('p.nbChart > 0')
            ->orderBy('p.' . $sortBy, $order)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return int
     */
    public function countActivePlayers(): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.nbChart > 0');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $sortBy
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return array<Player>
     */
    public function findForList(PlayerListFilter $filter, string $sortBy = 'pointGame', string $order = 'DESC', int $limit = 100, int $offset = 0): array
    {
        $validFields = ['pointGame', 'lastLogin', 'pseudo', 'createdAt', 'nbGame', 'nbChart', 'rankCup', 'rankMedal', 'pointChart', 'nbMasterBadge'];
        if (!in_array($sortBy, $validFields, true)) {
            $sortBy = 'pointGame';
        }

        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $qb = $this->buildListQuery($filter)
            ->addSelect('p')
            ->orderBy('p.' . $sortBy, $order)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function countForList(PlayerListFilter $filter): int
    {
        $qb = $this->buildListQuery($filter)
            ->select('COUNT(p.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function buildListQuery(PlayerListFilter $filter): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.team', 't')
            ->addSelect('t')
            ->leftJoin('p.country', 'c')
            ->addSelect('c')
            ->where('p.nbChart > 0');

        if ($filter->search !== null && $filter->search !== '') {
            $qb->andWhere('LOWER(p.pseudo) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $filter->search . '%');
        }

        if ($filter->countryId !== null) {
            $qb->andWhere('c.id = :countryId')
                ->setParameter('countryId', $filter->countryId);
        }

        if ($filter->teamId !== null) {
            $qb->andWhere('t.id = :teamId')
                ->setParameter('teamId', $filter->teamId);
        }

        if ($filter->platforms !== []) {
            // EXISTS (not a join) so matching several platforms never multiplies rows,
            // which would otherwise silently break the LIMIT/OFFSET pagination above.
            $sub = $this->getEntityManager()->createQueryBuilder()
                ->select('1')
                ->from(PlatformConnection::class, 'pc')
                ->where('pc.player = p')
                ->andWhere('pc.platform IN (:platforms)');

            $qb->andWhere($qb->expr()->exists($sub->getDQL()))
                ->setParameter('platforms', $filter->platforms);
        }

        if ($filter->pointsMin !== null) {
            $qb->andWhere('p.pointGame >= :pointsMin')
                ->setParameter('pointsMin', $filter->pointsMin);
        }

        if ($filter->pointsMax !== null) {
            $qb->andWhere('p.pointGame <= :pointsMax')
                ->setParameter('pointsMax', $filter->pointsMax);
        }

        if ($filter->neverActive) {
            $qb->andWhere('p.lastLogin IS NULL');
        } elseif ($filter->lastActiveSince !== null) {
            $qb->andWhere('p.lastLogin >= :lastActiveSince')
                ->setParameter('lastActiveSince', $filter->lastActiveSince);
        }

        if ($filter->hasBadges) {
            $qb->andWhere('p.nbMasterBadge > 0');
        }

        return $qb;
    }

    /**
     * @return array<Player>
     */
    public function findStaff(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.team', 't')
            ->addSelect('t')
            ->leftJoin('p.country', 'c')
            ->addSelect('c')
            ->where('p.status != :status')
            ->setParameter('status', PlayerStatusEnum::MEMBER)
            ->orderBy('p.pseudo', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
