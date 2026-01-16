<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\TeamBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Event\TeamBadgeLost;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Event\TeamBadgeObtained;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: TeamBadge::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: TeamBadge::class)]
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: TeamBadge::class)]
class TeamBadgeListener
{
    /** @var array<string, array<mixed>> */
    private array $changeSet = [];

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param TeamBadge $teamBadge
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(TeamBadge $teamBadge, PreUpdateEventArgs $event): void
    {
        $this->changeSet = $event->getEntityChangeSet();
    }

    /**
     * @param TeamBadge $teamBadge
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(TeamBadge $teamBadge, LifecycleEventArgs $event): void
    {
        if ($teamBadge->getBadge()->isTypeMaster() && array_key_exists('endedAt', $this->changeSet)) {
            $this->eventDispatcher->dispatch(new TeamBadgeLost($teamBadge));
        }
    }

    /**
     * @param TeamBadge $teamBadge
     * @param LifecycleEventArgs $event
     */
    public function postPersist(TeamBadge $teamBadge, LifecycleEventArgs $event): void
    {
        $this->eventDispatcher->dispatch(new TeamBadgeObtained($teamBadge));
    }
}
