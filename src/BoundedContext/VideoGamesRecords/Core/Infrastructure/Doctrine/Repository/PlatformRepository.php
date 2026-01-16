<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository;

use Doctrine\Persistence\ManagerRegistry;
use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;

class PlatformRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Platform::class);
    }

    /**
     * Finds all entities in the repository.
     * @return array<Platform> The entities.
     */
    public function findAll(): array
    {
        return $this->findBy([], ['name' => 'ASC']);
    }

    /**
     * @param string $q
     * @return mixed
     */
    public function autocomplete(string $q): mixed
    {
        $query = $this->createQueryBuilder('p');

        $query
            ->where("p.name LIKE :q")
            ->setParameter('q', '%' . $q . '%')
            ->orderBy("p.name", 'ASC');

        return $query->getQuery()->getResult();
    }
}
