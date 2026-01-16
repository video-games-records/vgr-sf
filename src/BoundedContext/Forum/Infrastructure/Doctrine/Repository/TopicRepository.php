<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
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
}
