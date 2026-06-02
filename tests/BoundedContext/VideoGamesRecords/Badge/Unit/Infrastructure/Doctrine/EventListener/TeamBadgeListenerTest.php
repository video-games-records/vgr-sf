<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\TeamBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Event\TeamBadgeObtained;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\EventListener\TeamBadgeListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TeamBadgeListenerTest extends TestCase
{
    public function testPostPersistDispatchesTeamBadgeObtainedEvent(): void
    {
        $teamBadge = $this->createMock(TeamBadge::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(
                fn (object $event) => $event instanceof TeamBadgeObtained
                    && $event->getTeamBadge() === $teamBadge
            ));

        $listener = new TeamBadgeListener($dispatcher);
        $listener->postPersist($teamBadge);
    }
}
