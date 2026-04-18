<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\GameDownloadUrl;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;

/**
 * @extends ServiceEntityRepository<GameDownloadUrl>
 */
class GameDownloadUrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameDownloadUrl::class);
    }

    /**
     * @return GameDownloadUrl[]
     */
    public function findByGame(Game $game): array
    {
        return $this->createQueryBuilder('gdu')
            ->andWhere('gdu.game = :game')
            ->setParameter('game', $game)
            ->orderBy('gdu.platform', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByGameAndPlatform(Game $game, Platform $platform): ?GameDownloadUrl
    {
        return $this->createQueryBuilder('gdu')
            ->andWhere('gdu.game = :game')
            ->andWhere('gdu.platform = :platform')
            ->setParameter('game', $game)
            ->setParameter('platform', $platform)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
