<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Video\Infrastructure\Doctrine\Repository;

use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\Video;
use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\VideoComment;

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

    /**
     * @return Paginator<VideoComment>
     */
    public function findPaginatedByVideo(Video $video, int $page, int $perPage): Paginator
    {
        $query = $this->createQueryBuilder('vc')
            ->select('vc, vcp')
            ->leftJoin('vc.player', 'vcp')
            ->where('vc.video = :video')
            ->setParameter('video', $video)
            ->orderBy('vc.createdAt', 'DESC');

        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return $paginator;
    }

    public function getCommentPositionById(VideoComment $comment, int $perPage): int
    {
        $position = (int) $this->createQueryBuilder('vc')
            ->select('COUNT(vc.id)')
            ->where('vc.video = :video')
            ->andWhere('vc.id >= :commentId')
            ->setParameter('video', $comment->getVideo())
            ->setParameter('commentId', $comment->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return max(1, (int) ceil($position / $perPage));
    }
}
