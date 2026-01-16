<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener;

use Doctrine\DBAL\Exception;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerStatusEnum;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Player::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Player::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Player::class)]
class PlayerListener
{
    /** @var array<string, array{0: mixed, 1: mixed}> */
    private array $changeSet = [];

    /**
     * @param Player             $player
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Player $player, LifecycleEventArgs $event): void
    {
        $player->setStatus(PlayerStatusEnum::MEMBER);
    }

    /**
     * @param Player $player
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Player $player, PreUpdateEventArgs $event): void
    {
        $this->changeSet = $event->getEntityChangeSet();
    }

    /**
     * @param Player             $player
     * @param LifecycleEventArgs $event
     * @return void
     * @throws Exception
     */
    public function postUpdate(Player $player, LifecycleEventArgs $event): void
    {
        if (array_key_exists('team', $this->changeSet)) {
            //@todo MAJ ranking team
        }
    }
}
