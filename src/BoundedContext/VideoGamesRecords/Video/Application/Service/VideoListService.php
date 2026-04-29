<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Video\Application\Service;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\Video;
use App\BoundedContext\VideoGamesRecords\Video\Infrastructure\Doctrine\Repository\VideoRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class VideoListService
{
    private const int VIDEOS_PER_PAGE = 12;

    public function __construct(
        private readonly VideoRepository $videoRepository
    ) {
    }

    /**
     * @return array{videos: Paginator<Video>, total_videos: int, has_more: bool, current_page: int, per_page: int}
     */
    public function getVideosPaginated(
        int $page = 1,
        ?Player $player = null,
        ?Game $game = null,
        string $orderBy = 'createdAt',
        string $orderDirection = 'DESC'
    ): array {
        $page = max(1, $page);

        $allowedFields = ['createdAt', 'viewCount', 'likeCount', 'title'];
        $allowedDirections = ['ASC', 'DESC'];
        $orderBy = in_array($orderBy, $allowedFields, true) ? $orderBy : 'createdAt';
        $orderDirection = in_array($orderDirection, $allowedDirections, true) ? $orderDirection : 'DESC';

        $queryBuilder = $this->createBaseQueryBuilder();

        if ($player) {
            $queryBuilder->andWhere('v.player = :player')
                ->setParameter('player', $player);
        }

        if ($game) {
            $queryBuilder->andWhere('v.game = :game')
                ->setParameter('game', $game);
        }

        $queryBuilder->orderBy('v.' . $orderBy, $orderDirection);

        $paginator = new Paginator($queryBuilder);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * self::VIDEOS_PER_PAGE)
            ->setMaxResults(self::VIDEOS_PER_PAGE);

        $totalResults = count($paginator);
        $hasMore = ($page * self::VIDEOS_PER_PAGE) < $totalResults;

        return [
            'videos' => $paginator,
            'total_videos' => $totalResults,
            'has_more' => $hasMore,
            'current_page' => $page,
            'per_page' => self::VIDEOS_PER_PAGE,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getVideosAsJson(
        int $page = 1,
        string $locale = 'en',
        ?Player $player = null,
        ?Game $game = null
    ): array {
        $result = $this->getVideosPaginated($page, $player, $game);

        $videos = [];
        foreach ($result['videos'] as $video) {
            $videos[] = $this->formatVideoForJson($video, $locale);
        }

        return [
            'videos' => $videos,
            'has_more' => $result['has_more'],
            'next_page' => $result['has_more'] ? $page + 1 : null,
        ];
    }

    private function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->videoRepository
            ->createQueryBuilder('v')
            ->select('v, p, g')
            ->leftJoin('v.player', 'p')
            ->leftJoin('v.game', 'g')
            ->where('v.isActive = :active')
            ->setParameter('active', true);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatVideoForJson(Video $video, string $locale): array
    {
        return [
            'id' => $video->getId(),
            'title' => $video->getTitle(),
            'slug' => $video->getSlug(),
            'thumbnail' => $video->getThumbnail(),
            'type' => $video->getType(),
            'viewCount' => $video->getViewCount(),
            'likeCount' => $video->getLikeCount(),
            'createdAt' => $video->getCreatedAt()?->format('d/m/Y') ?? '',
            'player' => [
                'pseudo' => $video->getPlayer()->getPseudo(),
            ],
            'game' => $video->getGame() ? [
                'name' => $video->getGame()->getName($locale),
            ] : null,
        ];
    }

    public function countVideosByGame(Game $game): int
    {
        $result = $this->videoRepository
            ->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.isActive = :active')
            ->andWhere('v.game = :game')
            ->setParameter('active', true)
            ->setParameter('game', $game)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result;
    }
}
