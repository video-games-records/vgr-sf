<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template TEntity of object
 * @extends ServiceEntityRepository<TEntity>
 */
abstract class DefaultRepository extends ServiceEntityRepository
{
    /** @var class-string<TEntity> */
    protected string $entityClass;

    /**
     * @param class-string<TEntity> $entityClass
     */
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        $this->entityClass = $entityClass;
        parent::__construct($registry, $entityClass);
    }

    /**
     * @param TEntity $object
     */
    public function save(object $object): void
    {
        $this->getEntityManager()->persist($object);
        $this->getEntityManager()->flush();
    }


    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @return TEntity|null
     * @throws ORMException
     */
    public function getReference(int|string $id): ?object
    {
        /** @var TEntity|null */
        return $this->getEntityManager()->getReference($this->entityClass, $id);
    }
}
