<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Domain\Event;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\TeamBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Event\TeamBadgeObtained;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class TeamBadgeObtainedTest extends TestCase
{
    public function testExtendsEvent(): void
    {
        $event = new TeamBadgeObtained($this->createMock(TeamBadge::class));
        $this->assertInstanceOf(Event::class, $event);
    }

    public function testGetTeamBadgeReturnsInjectedBadge(): void
    {
        $teamBadge = $this->createMock(TeamBadge::class);
        $event = new TeamBadgeObtained($teamBadge);

        $this->assertSame($teamBadge, $event->getTeamBadge());
    }
}
