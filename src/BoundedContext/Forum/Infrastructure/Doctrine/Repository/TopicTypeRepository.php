<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\Forum\Domain\Entity\TopicType;

/**
 * @extends ServiceEntityRepository<TopicType>
 */
class TopicTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TopicType::class);
    }

    public function save(TopicType $topicType): void
    {
        $this->getEntityManager()->persist($topicType);
        $this->getEntityManager()->flush();
    }

    public function remove(TopicType $topicType): void
    {
        $this->getEntityManager()->remove($topicType);
        $this->getEntityManager()->flush();
    }
}
