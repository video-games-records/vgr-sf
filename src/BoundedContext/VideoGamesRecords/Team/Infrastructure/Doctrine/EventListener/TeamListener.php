<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Team::class)]
class TeamListener
{
    /**
     * @param Team $team
     * @param LifecycleEventArgs $event
     * @phpstan-param LifecycleEventArgs<EntityManagerInterface> $event
     */
    public function postPersist(Team $team, LifecycleEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $player = $team->getLeader();
        $player->setTeam($team);
        $em->flush();
    }
}
