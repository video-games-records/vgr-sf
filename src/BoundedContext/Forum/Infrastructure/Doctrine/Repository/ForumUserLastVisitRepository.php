<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Doctrine\Repository;

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
}
