<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Domain\Event;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Event\Admin\AdminPlayerChartUpdated;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class AdminPlayerChartUpdatedTest extends TestCase
{
    public function testExtendsEvent(): void
    {
        $event = new AdminPlayerChartUpdated($this->createMock(PlayerChart::class));
        $this->assertInstanceOf(Event::class, $event);
    }

    public function testGetPlayerChartReturnsInjectedEntity(): void
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $event = new AdminPlayerChartUpdated($playerChart);

        $this->assertSame($playerChart, $event->getPlayerChart());
    }
}
