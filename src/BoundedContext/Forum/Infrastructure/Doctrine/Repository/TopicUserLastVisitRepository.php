<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Doctrine\Repository;

use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\Forum\Domain\Entity\TopicUserLastVisit;

/**
 * @extends ServiceEntityRepository<TopicUserLastVisit>
 */
class TopicUserLastVisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TopicUserLastVisit::class);
    }

    public function save(TopicUserLastVisit $visit): void
    {
        $this->getEntityManager()->persist($visit);
        $this->getEntityManager()->flush();
    }

    public function remove(TopicUserLastVisit $visit): void
    {
        $this->getEntityManager()->remove($visit);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array<int, TopicUserLastVisit> Indexed by topic ID
     */
    public function findByUserAndForumIndexedByTopic(User $user, Forum $forum): array
    {
        $visits = $this->createQueryBuilder('v')
            ->innerJoin('v.topic', 't')
            ->where('v.user = :user')
            ->andWhere('t.forum = :forum')
            ->setParameter('user', $user)
            ->setParameter('forum', $forum)
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($visits as $visit) {
            $indexed[$visit->getTopic()->getId()] = $visit;
        }

        return $indexed;
    }
}
