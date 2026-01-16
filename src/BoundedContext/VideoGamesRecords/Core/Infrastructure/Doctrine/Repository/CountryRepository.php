<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository;

use Doctrine\Persistence\ManagerRegistry;
use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;

class CountryRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Country::class);
    }

    /**
     * @return Country[]
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.translations', 't')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Country[]
     */
    public function findBySearchName(string $search): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.translations', 't')
            ->where('t.name LIKE :search')
            ->orWhere('c.codeIso2 LIKE :search')
            ->orWhere('c.codeIso3 LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
