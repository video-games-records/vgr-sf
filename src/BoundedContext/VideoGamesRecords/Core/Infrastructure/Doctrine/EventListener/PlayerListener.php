<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerStatusEnum;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Player::class)]
class PlayerListener
{
    /**
     * @param Player $player
     */
    public function prePersist(Player $player): void
    {
        $player->setStatus(PlayerStatusEnum::MEMBER);
    }
}
