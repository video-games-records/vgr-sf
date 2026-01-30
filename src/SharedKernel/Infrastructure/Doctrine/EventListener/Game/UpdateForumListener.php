<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Doctrine\EventListener\Game;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;

#[AsEntityListener(event: Events::preUpdate, method: 'updateForum', entity: Game::class)]
readonly class UpdateForumListener
{
    public function updateForum(Game $game): void
    {
        $game->getForum()->setLibForum($game->getlibGameEn());
    }
}
