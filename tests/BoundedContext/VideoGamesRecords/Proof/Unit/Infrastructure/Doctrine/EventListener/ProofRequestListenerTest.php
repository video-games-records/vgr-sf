<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Proof\Unit\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\ProofRequest;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofRequestAccepted;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofRequestRefused;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofRequestStatus;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\EventListener\ProofRequestListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProofRequestListenerTest extends TestCase
{
    /** @param array<string, array{0: mixed, 1: mixed}|mixed> $changeSet */
    private function makePreUpdateArgs(array $changeSet): PreUpdateEventArgs
    {
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->method('getEntityChangeSet')->willReturn($changeSet);
        return $args;
    }

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

        $listener = new ProofRequestListener($this->createMock(EventDispatcherInterface::class));
        $listener->postPersist($proofRequest, $lifecycleArgs);
    }

    public function testPostUpdateDispatchesProofRequestAcceptedWhenAccepted(): void
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->expects($this->once())->method('setStatus')->with(PlayerChartStatusEnum::REQUEST_VALIDATED);

        $proofRequest = $this->createMock(ProofRequest::class);
        $proofRequest->method('getPlayerChart')->willReturn($playerChart);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn (object $e) => $e instanceof ProofRequestAccepted));

        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($this->createMock(EntityManagerInterface::class));

        $listener = new ProofRequestListener($dispatcher);
        $listener->preUpdate($proofRequest, $this->makePreUpdateArgs([
            'status' => [ProofRequestStatus::IN_PROGRESS, ProofRequestStatus::ACCEPTED],
        ]));
        $listener->postUpdate($proofRequest, $lifecycleArgs);
    }

    public function testPostUpdateDispatchesProofRequestRefusedWhenRefused(): void
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->expects($this->once())->method('setStatus')->with(PlayerChartStatusEnum::NONE);

        $proofRequest = $this->createMock(ProofRequest::class);
        $proofRequest->method('getPlayerChart')->willReturn($playerChart);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn (object $e) => $e instanceof ProofRequestRefused));

        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($this->createMock(EntityManagerInterface::class));

        $listener = new ProofRequestListener($dispatcher);
        $listener->preUpdate($proofRequest, $this->makePreUpdateArgs([
            'status' => [ProofRequestStatus::IN_PROGRESS, ProofRequestStatus::REFUSED],
        ]));
        $listener->postUpdate($proofRequest, $lifecycleArgs);
    }

    public function testPostUpdateDoesNothingWhenStatusNotChanged(): void
    {
        $proofRequest = $this->createMock(ProofRequest::class);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->never())->method('dispatch');

        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($this->createMock(EntityManagerInterface::class));

        $listener = new ProofRequestListener($dispatcher);
        $listener->preUpdate($proofRequest, $this->makePreUpdateArgs([]));
        $listener->postUpdate($proofRequest, $lifecycleArgs);
    }
}
