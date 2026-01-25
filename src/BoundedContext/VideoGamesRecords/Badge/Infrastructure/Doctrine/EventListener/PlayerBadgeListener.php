<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Event\PlayerBadgeObtained;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: PlayerBadge::class)]
class PlayerBadgeListener
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param PlayerBadge $playerBadge
     */
    public function postPersist(PlayerBadge $playerBadge): void
    {
        $this->eventDispatcher->dispatch(new PlayerBadgeObtained($playerBadge));
    }
}
