<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Video\Unit\Application\Service;

use App\BoundedContext\VideoGamesRecords\Video\Application\Service\VideoRecommendationService;
use App\BoundedContext\VideoGamesRecords\Video\Application\Service\VideoRelevanceScorer;
use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\Video;
use App\BoundedContext\VideoGamesRecords\Video\Infrastructure\Doctrine\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class VideoRecommendationServiceTest extends TestCase
{
    private function makeService(
        EntityManagerInterface $em,
        VideoRepository $repo,
        CacheItemPoolInterface $cache,
    ): VideoRecommendationService {
        return new VideoRecommendationService(
            $em,
            $repo,
            $cache,
            $this->createMock(LoggerInterface::class),
            new VideoRelevanceScorer(),
        );
    }

    public function testGetRelatedVideosReturnsCachedResultOnCacheHit(): void
    {
        $video = $this->createMock(Video::class);
        $video->method('getId')->willReturn(42);

        $cachedVideo = $this->createMock(Video::class);

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(true);
        $cacheItem->method('get')->willReturn([99]);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn($cacheItem);

        $repo = $this->createMock(VideoRepository::class);
        $repo->method('findByIdsWithPlayer')->with([99])->willReturn([$cachedVideo]);

        $em = $this->createMock(EntityManagerInterface::class);

        $result = $this->makeService($em, $repo, $cache)->getRelatedVideos($video, 1);

        $this->assertSame([$cachedVideo], $result);
    }

    public function testGetRelatedVideosDoesNotCallRepositoryWhenCacheHits(): void
    {
        $video = $this->createMock(Video::class);
        $video->method('getId')->willReturn(1);

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(true);
        $cacheItem->method('get')->willReturn([]);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn($cacheItem);

        $repo = $this->createMock(VideoRepository::class);
        $repo->expects($this->never())->method('createQueryBuilder');
        $repo->method('findByIdsWithPlayer')->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);

        $this->makeService($em, $repo, $cache)->getRelatedVideos($video, 5);
    }

    public function testClearVideoRecommendationsCacheDeletesCorrectKey(): void
    {
        $video = $this->createMock(Video::class);
        $video->method('getId')->willReturn(7);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->expects($this->once())
            ->method('deleteItem')
            ->with('video_recommendations_7')
            ->willReturn(true);

        $repo = $this->createMock(VideoRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $result = $this->makeService($em, $repo, $cache)->clearVideoRecommendationsCache($video);

        $this->assertTrue($result);
    }

    public function testClearVideoRecommendationsCacheReturnsFalseWhenDeleteFails(): void
    {
        $video = $this->createMock(Video::class);
        $video->method('getId')->willReturn(3);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('deleteItem')->willReturn(false);

        $repo = $this->createMock(VideoRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $result = $this->makeService($em, $repo, $cache)->clearVideoRecommendationsCache($video);

        $this->assertFalse($result);
    }
}
