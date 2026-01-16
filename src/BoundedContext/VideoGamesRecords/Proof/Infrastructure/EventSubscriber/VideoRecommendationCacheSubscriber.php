<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Video;
use App\BoundedContext\VideoGamesRecords\Proof\Application\Service\VideoRecommendationService;

class VideoRecommendationCacheSubscriber implements EventSubscriber
{
    public function __construct(
        private readonly VideoRecommendationService $videoRecommendationService
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    /**
     * @param LifecycleEventArgs<EntityManagerInterface> $args
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Video) {
            $this->videoRecommendationService->clearVideoRecommendationsCache($entity);
        }
    }

    /**
     * @param LifecycleEventArgs<EntityManagerInterface> $args
     */
    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Video) {
            $this->videoRecommendationService->clearVideoRecommendationsCache($entity);
        }
    }
}
