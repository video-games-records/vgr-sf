<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Proof\Unit\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofStatus;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\EventListener\ProofListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;

class ProofListenerTest extends TestCase
{
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

        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($em);

        $listener = new ProofListener();
        $listener->postUpdate($proof, $lifecycleArgs);
    }

    public function testPostUpdateDoesNothingWhenStatusIsNotClosed(): void
    {
        $proof = $this->createMock(Proof::class);
        $proof->method('getStatus')->willReturn(ProofStatus::IN_PROGRESS);
        $proof->expects($this->never())->method('getPlayerChart');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($em);

        $listener = new ProofListener();
        $listener->postUpdate($proof, $lifecycleArgs);
    }

    public function testPostUpdateDoesNothingWhenPlayerChartIsNull(): void
    {
        $proof = $this->createMock(Proof::class);
        $proof->method('getStatus')->willReturn(ProofStatus::CLOSED);
        $proof->method('getPlayerChart')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($em);

        $listener = new ProofListener();
        $listener->postUpdate($proof, $lifecycleArgs);
    }
}
