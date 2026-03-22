<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\MasterBadge;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: MasterBadge::class)]
class MasterBadgeListener
{
    public function preUpdate(MasterBadge $badge, PreUpdateEventArgs $event): void
    {
        if (!$event->hasChangedField('picture')) {
            return;
        }

        $game = $badge->getGame();
        if ($game === null || $game->getCompletionBadge() === null) {
            return;
        }

        $game->getCompletionBadge()->setPicture($event->getNewValue('picture'));
    }
}
