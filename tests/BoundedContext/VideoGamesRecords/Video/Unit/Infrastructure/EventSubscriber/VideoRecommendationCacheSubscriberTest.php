<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Video\Unit\Infrastructure\EventSubscriber;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Video\Application\Service\VideoRecommendationService;
use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\Video;
use App\BoundedContext\VideoGamesRecords\Video\Infrastructure\EventSubscriber\VideoRecommendationCacheSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;

class VideoRecommendationCacheSubscriberTest extends TestCase
{
    public function testGetSubscribedEventsContainsPostUpdateAndPostRemove(): void
    {
        $subscriber = new VideoRecommendationCacheSubscriber(
            $this->createMock(VideoRecommendationService::class)
        );

        $events = $subscriber->getSubscribedEvents();

        $this->assertContains(Events::postUpdate, $events);
        $this->assertContains(Events::postRemove, $events);
    }

    public function testPostUpdateClearsVideoCacheForVideoEntity(): void
    {
        $video = $this->createMock(Video::class);

        $service = $this->createMock(VideoRecommendationService::class);
        $service->expects($this->once())
            ->method('clearVideoRecommendationsCache')
            ->with($video);

        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn($video);

        (new VideoRecommendationCacheSubscriber($service))->postUpdate($args);
    }

    public function testPostUpdateIgnoresNonVideoEntities(): void
    {
        $service = $this->createMock(VideoRecommendationService::class);
        $service->expects($this->never())->method('clearVideoRecommendationsCache');

        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn($this->createMock(Player::class));

        (new VideoRecommendationCacheSubscriber($service))->postUpdate($args);
    }

    public function testPostRemoveClearsVideoCacheForVideoEntity(): void
    {
        $video = $this->createMock(Video::class);

        $service = $this->createMock(VideoRecommendationService::class);
        $service->expects($this->once())
            ->method('clearVideoRecommendationsCache')
            ->with($video);

        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn($video);

        (new VideoRecommendationCacheSubscriber($service))->postRemove($args);
    }

    public function testPostRemoveIgnoresNonVideoEntities(): void
    {
        $service = $this->createMock(VideoRecommendationService::class);
        $service->expects($this->never())->method('clearVideoRecommendationsCache');

        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn($this->createMock(Player::class));

        (new VideoRecommendationCacheSubscriber($service))->postRemove($args);
    }
}
