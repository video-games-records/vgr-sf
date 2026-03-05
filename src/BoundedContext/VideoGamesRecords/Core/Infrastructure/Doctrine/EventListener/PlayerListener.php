<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use HTMLPurifier;
use HTMLPurifier_Config;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Player::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Player::class)]
class PlayerListener
{
    private HTMLPurifier $purifier;

    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,strong,em,u,ol,ul,li,a[href],h1,h2,h3,blockquote');
        $this->purifier = new HTMLPurifier($config);
    }

    public function prePersist(Player $player): void
    {
        $player->setStatus(PlayerStatusEnum::MEMBER);
        $this->purifyPersonalData($player);
    }

    public function preUpdate(Player $player): void
    {
        $this->purifyPersonalData($player);
    }

    private function purifyPersonalData(Player $player): void
    {
        if ($player->getPresentation()) {
            $player->setPresentation($this->purifier->purify($player->getPresentation()));
        }
        if ($player->getCollection()) {
            $player->setCollection($this->purifier->purify($player->getCollection()));
        }
    }
}
