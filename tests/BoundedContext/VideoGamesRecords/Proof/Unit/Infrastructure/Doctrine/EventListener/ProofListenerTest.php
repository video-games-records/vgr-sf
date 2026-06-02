<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Proof\Unit\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofAccepted;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofRefused;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofStatus;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\EventListener\ProofListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProofListenerTest extends TestCase
{
    /** @param array<string, array{0: mixed, 1: mixed}|mixed> $changeSet */
    private function makePreUpdateArgs(array $changeSet): PreUpdateEventArgs
    {
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->method('getEntityChangeSet')->willReturn($changeSet);
        return $args;
    }

    public function testPostUpdateDispatchesProofAcceptedWhenTransitioningToAccepted(): void
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->expects($this->once())->method('setStatus')->with(PlayerChartStatusEnum::PROVED);

        $proof = $this->createMock(Proof::class);
        $proof->method('getPlayerChart')->willReturn($playerChart);
        $proof->method('getStatus')->willReturn(ProofStatus::ACCEPTED);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn (object $e) => $e instanceof ProofAccepted));

        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($this->createMock(EntityManagerInterface::class));

        $listener = new ProofListener($this->createMock(UserProvider::class), $dispatcher);
        $listener->preUpdate($proof, $this->makePreUpdateArgs([
            'status' => [ProofStatus::IN_PROGRESS, ProofStatus::ACCEPTED],
        ]));
        $listener->postUpdate($proof, $lifecycleArgs);
    }

    public function testPostUpdateDispatchesProofRefusedWhenTransitioningToRefused(): void
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->method('getStatus')->willReturn(PlayerChartStatusEnum::PROOF_SENT);
        $playerChart->expects($this->once())->method('setStatus');

        $proof = $this->createMock(Proof::class);
        $proof->method('getPlayerChart')->willReturn($playerChart);
        $proof->method('getStatus')->willReturn(ProofStatus::REFUSED);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn (object $e) => $e instanceof ProofRefused));

        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($this->createMock(EntityManagerInterface::class));

        $listener = new ProofListener($this->createMock(UserProvider::class), $dispatcher);
        $listener->preUpdate($proof, $this->makePreUpdateArgs([
            'status' => [ProofStatus::IN_PROGRESS, ProofStatus::REFUSED],
        ]));
        $listener->postUpdate($proof, $lifecycleArgs);
    }

    public function testPostUpdateDoesNothingWhenStatusUnchanged(): void
    {
        $proof = $this->createMock(Proof::class);
        $proof->method('getPlayerChart')->willReturn(null);
        $proof->method('getStatus')->willReturn(ProofStatus::IN_PROGRESS);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->never())->method('dispatch');

        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($this->createMock(EntityManagerInterface::class));

        $listener = new ProofListener($this->createMock(UserProvider::class), $dispatcher);
        $listener->preUpdate($proof, $this->makePreUpdateArgs([]));
        $listener->postUpdate($proof, $lifecycleArgs);
    }

    public function testPostUpdateSetsPlayerChartToNullOnClosed(): void
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->method('getStatus')->willReturn(PlayerChartStatusEnum::PROVED);
        $playerChart->expects($this->once())->method('setProof')->with(null);
        $playerChart->expects($this->once())->method('setStatus')->with(PlayerChartStatusEnum::NONE);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $proof = $this->createMock(Proof::class);
        $proof->method('getPlayerChart')->willReturn($playerChart);
        $proof->method('getStatus')->willReturn(ProofStatus::CLOSED);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->never())->method('dispatch');

        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($em);

        $listener = new ProofListener($this->createMock(UserProvider::class), $dispatcher);
        $listener->preUpdate($proof, $this->makePreUpdateArgs([]));
        $listener->postUpdate($proof, $lifecycleArgs);
    }
}
