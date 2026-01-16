<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository;

use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Platform;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Platform>
 */
class PlatformRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Platform::class);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function findByIgdbData(array $data): ?Platform
    {
        return $this->find($data['id']);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFromIgdbData(array $data): Platform
    {
        $platform = new Platform();
        $this->updateFromIgdbData($platform, $data);

        return $platform;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateFromIgdbData(Platform $platform, array $data): void
    {
        $platform->setId($data['id']);
        $platform->setName($data['name']);
        $platform->setAbbreviation($data['abbreviation'] ?? null);
        $platform->setAlternativeName($data['alternative_name'] ?? null);
        $platform->setGeneration($data['generation'] ?? null);
        $platform->setSlug($data['slug'] ?? null);
        $platform->setSummary($data['summary'] ?? null);
        $platform->setUrl($data['url'] ?? null);
        $platform->setChecksum($data['checksum'] ?? null);

        if (isset($data['updated_at'])) {
            $platform->setUpdatedAt(new \DateTimeImmutable('@' . $data['updated_at']));
        }

        if (isset($data['created_at'])) {
            $platform->setCreatedAt(new \DateTimeImmutable('@' . $data['created_at']));
        }
    }
}
