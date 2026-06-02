<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Domain\Event;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Event\PlayerMasterBadgeLost;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class PlayerMasterBadgeLostTest extends TestCase
{
    public function testExtendsEvent(): void
    {
        $event = new PlayerMasterBadgeLost(
            $this->createMock(PlayerBadge::class),
            $this->createMock(Game::class)
        );
        $this->assertInstanceOf(Event::class, $event);
    }

    public function testGetPlayerBadgeReturnsInjectedBadge(): void
    {
        $playerBadge = $this->createMock(PlayerBadge::class);
        $game = $this->createMock(Game::class);

        $event = new PlayerMasterBadgeLost($playerBadge, $game);

        $this->assertSame($playerBadge, $event->getPlayerBadge());
    }

    public function testGetGameReturnsInjectedGame(): void
    {
        $playerBadge = $this->createMock(PlayerBadge::class);
        $game = $this->createMock(Game::class);

        $event = new PlayerMasterBadgeLost($playerBadge, $game);

        $this->assertSame($game, $event->getGame());
    }
}
