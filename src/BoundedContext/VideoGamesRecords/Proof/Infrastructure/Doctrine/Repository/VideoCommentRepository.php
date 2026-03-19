<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository;

use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\VideoComment;

/**
 * @extends DefaultRepository<VideoComment>
 */
class VideoCommentRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VideoComment::class);
    }

    /**
     * @return VideoComment[]
     */
    public function findByVideoPaginated(int $videoId, int $offset, int $limit): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.video', 'v')
            ->join('c.player', 'p')
            ->addSelect('p')
            ->where('v.id = :videoId')
            ->setParameter('videoId', $videoId)
            ->orderBy('c.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByVideo(int $videoId): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.video', 'v')
            ->where('v.id = :videoId')
            ->setParameter('videoId', $videoId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
