<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Infrastructure\Persistence\Doctrine;

use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return User[]
     */
    public function autocomplete(string $q): array
    {
        $query = $this->createQueryBuilder('u');

        $query
            ->where('u.username LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            //->andWhere('u.enabled = 1')
            ->orderBy('u.username', 'ASC');

        return $query->getQuery()->getResult();
    }

    public function save(User $object): void
    {
        $this->getEntityManager()->persist($object);
        $this->getEntityManager()->flush();
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
