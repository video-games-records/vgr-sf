<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Infrastructure\Doctrine\Repository;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Entity\PlatformConnection;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Repository\PlatformConnectionRepositoryInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlatformConnection>
 */
class PlatformConnectionRepository extends ServiceEntityRepository implements PlatformConnectionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlatformConnection::class);
    }

    public function findByPlayerAndPlatform(Player $player, PlatformEnum $platform): ?PlatformConnection
    {
        return $this->findOneBy(['player' => $player, 'platform' => $platform]);
    }

    /** @return list<PlatformConnection> */
    public function findByPlayer(Player $player): array
    {
        /** @var list<PlatformConnection> */
        return $this->findBy(['player' => $player]);
    }

    public function save(PlatformConnection $connection): void
    {
        $this->getEntityManager()->persist($connection);
        $this->getEntityManager()->flush();
    }

    public function remove(PlatformConnection $connection): void
    {
        $this->getEntityManager()->remove($connection);
        $this->getEntityManager()->flush();
    }
}
