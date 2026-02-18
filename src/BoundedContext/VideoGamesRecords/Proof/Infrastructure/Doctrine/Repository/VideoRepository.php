<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository;

use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Video;

/**
 * @extends DefaultRepository<Video>
 */
class VideoRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    /**
     * @return Video[]
     */
    public function findActiveVideosPaginated(int $offset, int $limit): array
    {
        return $this->createQueryBuilder('v')
            ->join('v.player', 'p')
            ->addSelect('p')
            ->leftJoin('v.game', 'g')
            ->addSelect('g')
            ->where('v.isActive = true')
            ->orderBy('v.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countActiveVideos(): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.isActive = true')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
