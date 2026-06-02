<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Domain\Event;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Event\PlayerBadgeObtained;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class PlayerBadgeObtainedTest extends TestCase
{
    public function testExtendsEvent(): void
    {
        $event = new PlayerBadgeObtained($this->createMock(PlayerBadge::class));
        $this->assertInstanceOf(Event::class, $event);
    }

    public function testGetPlayerBadgeReturnsInjectedBadge(): void
    {
        $playerBadge = $this->createMock(PlayerBadge::class);
        $event = new PlayerBadgeObtained($playerBadge);

        $this->assertSame($playerBadge, $event->getPlayerBadge());
    }
}
