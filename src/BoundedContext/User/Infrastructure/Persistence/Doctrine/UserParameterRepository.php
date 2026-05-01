<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Infrastructure\Persistence\Doctrine;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Domain\Entity\UserParameter;
use App\BoundedContext\User\Domain\ValueObject\UserParameterKeyEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserParameter>
 */
class UserParameterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserParameter::class);
    }

    /**
     * @return array<string, UserParameter> indexed by param_key value
     */
    public function findAllByUser(User $user): array
    {
        $results = $this->createQueryBuilder('up')
            ->where('up.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($results as $parameter) {
            $indexed[$parameter->getParamKey()->value] = $parameter;
        }

        return $indexed;
    }

    public function findOneByUserAndKey(User $user, UserParameterKeyEnum $key): ?UserParameter
    {
        return $this->createQueryBuilder('up')
            ->where('up.user = :user')
            ->andWhere('up.paramKey = :key')
            ->setParameter('user', $user)
            ->setParameter('key', $key->value)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
