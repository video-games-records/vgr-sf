<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamRequest;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: TeamRequest::class)]
class TeamRequestListener
{
    /**
     * @param TeamRequest $teamRequest
     * @param LifecycleEventArgs $event
     * @phpstan-param LifecycleEventArgs<EntityManagerInterface> $event
     */
    public function postUpdate(TeamRequest $teamRequest, LifecycleEventArgs $event): void
    {
        $em = $event->getObjectManager();
        if ($teamRequest->getTeamRequestStatus()->isAccepted()) {
            $player = $teamRequest->getPlayer();
            $player->setTeam($teamRequest->getTeam());
            $em->flush();
        }
    }
}
