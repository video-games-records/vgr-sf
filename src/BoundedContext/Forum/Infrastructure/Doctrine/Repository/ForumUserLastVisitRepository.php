<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Doctrine\Repository;

use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\Forum\Domain\Entity\ForumUserLastVisit;

/**
 * @extends ServiceEntityRepository<ForumUserLastVisit>
 */
class ForumUserLastVisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForumUserLastVisit::class);
    }

    public function save(ForumUserLastVisit $visit): void
    {
        $this->getEntityManager()->persist($visit);
        $this->getEntityManager()->flush();
    }

    public function remove(ForumUserLastVisit $visit): void
    {
        $this->getEntityManager()->remove($visit);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array<int, ForumUserLastVisit> Indexed by forum ID
     */
    public function findByUserIndexedByForum(User $user): array
    {
        $visits = $this->createQueryBuilder('v')
            ->where('v.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($visits as $visit) {
            $indexed[$visit->getForum()->getId()] = $visit;
        }

        return $indexed;
    }
}
