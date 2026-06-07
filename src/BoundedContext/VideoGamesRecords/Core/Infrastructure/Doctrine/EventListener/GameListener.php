<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\MasterBadge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Game::class)]
class GameListener
{
    public function prePersist(Game $game): void
    {
        if (null == $game->getLibGameFr()) {
            $game->setLibGameFr($game->getLibGameEn());
        }

        $badge = new MasterBadge();
        $badge->setPicture('master_default.gif');
        $game->setBadge($badge);
    }
}
