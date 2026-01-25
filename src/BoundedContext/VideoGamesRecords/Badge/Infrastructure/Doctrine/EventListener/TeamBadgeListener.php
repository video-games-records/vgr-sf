<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\TeamBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Event\TeamBadgeObtained;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: TeamBadge::class)]
class TeamBadgeListener
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param TeamBadge $teamBadge
     */
    public function postPersist(TeamBadge $teamBadge): void
    {
        $this->eventDispatcher->dispatch(new TeamBadgeObtained($teamBadge));
    }
}
