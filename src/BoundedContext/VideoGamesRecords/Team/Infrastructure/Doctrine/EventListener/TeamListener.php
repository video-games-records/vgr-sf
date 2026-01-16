<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Team::class)]
class TeamListener
{
    private UserProvider $userProvider;

    /**
     * @param UserProvider $userProvider
     */
    public function __construct(UserProvider $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * @param Team $team
     * @param LifecycleEventArgs $event
     * @return void
     * @throws ORMException
     */
    public function prePersist(Team $team, LifecycleEventArgs $event): void
    {
        $team->setLeader($this->userProvider->getPlayer());
    }


    /**
     * @param Team       $team
     * @param LifecycleEventArgs $event
     */
    public function postPersist(Team $team, LifecycleEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $player = $team->getLeader();
        $player->setTeam($team);
        $em->flush();
    }
}
