<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\SerieBadge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Serie::class)]
class SerieListener
{
    public function prePersist(Serie $serie): void
    {
        $badge = new SerieBadge();
        $badge->setPicture('default.gif');
        $serie->setBadge($badge);
    }
}
