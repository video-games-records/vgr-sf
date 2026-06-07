<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Proof\Unit\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\ProofRequest;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\EventListener\ProofRequestListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;

class ProofRequestListenerTest extends TestCase
{
    public function testPostPersistSetsPlayerChartToRequestPendingAndFlushes(): void
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->expects($this->once())->method('setStatus')->with(PlayerChartStatusEnum::REQUEST_PENDING);

        $proofRequest = $this->createMock(ProofRequest::class);
        $proofRequest->method('getPlayerChart')->willReturn($playerChart);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($em);

        $listener = new ProofRequestListener();
        $listener->postPersist($proofRequest, $lifecycleArgs);
    }
}
