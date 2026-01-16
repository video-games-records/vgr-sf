<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Doctrine\Repository;

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
}
