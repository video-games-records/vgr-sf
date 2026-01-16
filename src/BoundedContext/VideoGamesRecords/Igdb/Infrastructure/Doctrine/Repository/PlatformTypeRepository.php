<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository;

use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\PlatformType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlatformType>
 */
class PlatformTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlatformType::class);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function findByIgdbData(array $data): ?PlatformType
    {
        return $this->find($data['id']);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFromIgdbData(array $data): PlatformType
    {
        $platformType = new PlatformType();
        $this->updateFromIgdbData($platformType, $data);

        return $platformType;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateFromIgdbData(PlatformType $platformType, array $data): void
    {
        $platformType->setId($data['id']);
        $platformType->setName($data['name']);
        $platformType->setChecksum($data['checksum'] ?? null);

        if (isset($data['updated_at'])) {
            $platformType->setUpdatedAt(new \DateTimeImmutable('@' . $data['updated_at']));
        }

        if (isset($data['created_at'])) {
            $platformType->setCreatedAt(new \DateTimeImmutable('@' . $data['created_at']));
        }
    }
}
