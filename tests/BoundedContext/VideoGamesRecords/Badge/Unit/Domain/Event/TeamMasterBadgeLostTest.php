<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Domain\Event;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\TeamBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Event\TeamMasterBadgeLost;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class TeamMasterBadgeLostTest extends TestCase
{
    public function testExtendsEvent(): void
    {
        $event = new TeamMasterBadgeLost(
            $this->createMock(TeamBadge::class),
            $this->createMock(Game::class)
        );
        $this->assertInstanceOf(Event::class, $event);
    }

    public function testGetTeamBadgeReturnsInjectedBadge(): void
    {
        $teamBadge = $this->createMock(TeamBadge::class);
        $game = $this->createMock(Game::class);

        $event = new TeamMasterBadgeLost($teamBadge, $game);

        $this->assertSame($teamBadge, $event->getTeamBadge());
    }

    public function testGetGameReturnsInjectedGame(): void
    {
        $teamBadge = $this->createMock(TeamBadge::class);
        $game = $this->createMock(Game::class);

        $event = new TeamMasterBadgeLost($teamBadge, $game);

        $this->assertSame($game, $event->getGame());
    }
}
