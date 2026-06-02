<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Event\PlayerBadgeObtained;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\EventListener\PlayerBadgeListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerBadgeListenerTest extends TestCase
{
    public function testPostPersistDispatchesPlayerBadgeObtainedEvent(): void
    {
        $playerBadge = $this->createMock(PlayerBadge::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(
                fn (object $event) => $event instanceof PlayerBadgeObtained
                    && $event->getPlayerBadge() === $playerBadge
            ));

        $listener = new PlayerBadgeListener($dispatcher);
        $listener->postPersist($playerBadge);
    }
}
