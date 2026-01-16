<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Group::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Group::class)]
class GroupListener
{
    /**
     * @param Group       $group
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Group $group, LifecycleEventArgs $event): void
    {
        if (null == $group->getLibGroupFr()) {
            $group->setLibGroupFr($group->getLibGroupEn());
        }
    }

    /**
     * @param Group       $group
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Group $group, PreUpdateEventArgs $event): void
    {
        if (null == $group->getLibGroupFr()) {
            $group->setLibGroupFr($group->getLibGroupEn());
        }
    }
}
