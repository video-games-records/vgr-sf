<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Player::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Player::class)]
class PlayerListener
{
    public function __construct(
        #[Autowire(service: 'html_sanitizer.sanitizer.app.content_sanitizer')]
        private readonly HtmlSanitizerInterface $sanitizer,
    ) {
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
            $player->setPresentation($this->sanitizer->sanitize($player->getPresentation()));
        }
        if ($player->getCollection()) {
            $player->setCollection($this->sanitizer->sanitize($player->getCollection()));
        }
    }
}
