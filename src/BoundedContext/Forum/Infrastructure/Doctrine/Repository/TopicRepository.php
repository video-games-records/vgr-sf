<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Doctrine\Repository;

use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\Forum\Domain\Entity\Topic;

/**
 * @extends ServiceEntityRepository<Topic>
 */
class TopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Topic::class);
    }

    public function save(Topic $topic): void
    {
        $this->getEntityManager()->persist($topic);
        $this->getEntityManager()->flush();
    }

    public function remove(Topic $topic): void
    {
        $this->getEntityManager()->remove($topic);
        $this->getEntityManager()->flush();
    }

    /** @return Query<mixed, mixed> */
    public function getActiveTopicsQuery(Forum $forum): Query
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.type', 'tt')
            ->addSelect('tt')
            ->leftJoin('t.lastMessage', 'lm')
            ->addSelect('lm')
            ->leftJoin('lm.user', 'lmu')
            ->addSelect('lmu')
            ->leftJoin('t.user', 'tu')
            ->addSelect('tu')
            ->where('t.forum = :forum')
            ->andWhere('t.boolArchive = :archived')
            ->setParameter('forum', $forum)
            ->setParameter('archived', false)
            ->orderBy('tt.position', 'ASC')
            ->addOrderBy('t.updatedAt', 'DESC')
            ->getQuery();
    }

    /**
     * @return Topic[]
     */
    /**
     * @return Topic[]
     */
    public function findWithRecentActivity(int $days = 7, int $limit = 20, ?User $user = null): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.lastMessage', 'lm')
            ->innerJoin('t.forum', 'f')
            ->leftJoin('lm.user', 'lmu')
            ->where('t.boolArchive = :archived')
            ->andWhere('lm.createdAt >= :date')
            ->setParameter('archived', false)
            ->setParameter('date', $date)
            ->orderBy('lm.createdAt', 'DESC')
            ->setMaxResults($limit);

        if ($user !== null) {
            $qb->leftJoin('t.userLastVisits', 'ulv', 'WITH', 'ulv.user = :user')
               ->addSelect('ulv')
               ->setParameter('user', $user);
        }

        return $qb->getQuery()->getResult();
    }
}
