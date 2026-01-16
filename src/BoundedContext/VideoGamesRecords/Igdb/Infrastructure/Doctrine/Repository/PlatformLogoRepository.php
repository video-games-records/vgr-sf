<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository;

use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\PlatformLogo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlatformLogo>
 */
class PlatformLogoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlatformLogo::class);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function findByIgdbData(array $data): ?PlatformLogo
    {
        return $this->find($data['id']);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFromIgdbData(array $data): PlatformLogo
    {
        $platformLogo = new PlatformLogo();
        $this->updateFromIgdbData($platformLogo, $data);

        return $platformLogo;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateFromIgdbData(PlatformLogo $platformLogo, array $data): void
    {
        $platformLogo->setId($data['id']);
        $platformLogo->setAlphaChannel($data['alpha_channel'] ?? false);
        $platformLogo->setAnimated($data['animated'] ?? false);
        $platformLogo->setChecksum($data['checksum'] ?? null);
        $platformLogo->setHeight($data['height'] ?? 0);
        $platformLogo->setImageId($data['image_id'] ?? '');
        $platformLogo->setUrl($data['url'] ?? null);
        $platformLogo->setWidth($data['width'] ?? 0);

        if (isset($data['updated_at'])) {
            $platformLogo->setUpdatedAt(new \DateTimeImmutable('@' . $data['updated_at']));
        }

        if (isset($data['created_at'])) {
            $platformLogo->setCreatedAt(new \DateTimeImmutable('@' . $data['created_at']));
        }
    }
}
