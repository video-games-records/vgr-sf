<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository;

use Doctrine\ORM\NonUniqueResultException;
use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\User\Domain\Entity\User;

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
    public function getPlayerFromUser(User $user): Player
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
     * @return int|mixed|string
     */
    public function getProofStats(): mixed
    {
        $query = $this->createQueryBuilder('player')
            ->select('player.id as idPlayer, player.pseudo')
            ->innerJoin('player.proofRespondings', 'proof')
            ->addSelect('COUNT(proof.id) as nb, SUBSTRING(proof.checkedAt, 1, 7) as month')
            ->where("proof.checkedAt > '2020-01-01'")
            ->orderBy('month', 'DESC')
            ->groupBy('player.id')
            ->addGroupBy('month');
        return $query->getQuery()->getResult(2);
    }
}
