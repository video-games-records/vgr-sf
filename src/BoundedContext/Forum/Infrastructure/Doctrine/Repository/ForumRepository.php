<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\Forum\Domain\Entity\Forum;

/**
 * @extends ServiceEntityRepository<Forum>
 */
class ForumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Forum::class);
    }

    public function save(Forum $forum): void
    {
        $this->getEntityManager()->persist($forum);
        $this->getEntityManager()->flush();
    }

    public function remove(Forum $forum): void
    {
        $this->getEntityManager()->remove($forum);
        $this->getEntityManager()->flush();
    }
}
