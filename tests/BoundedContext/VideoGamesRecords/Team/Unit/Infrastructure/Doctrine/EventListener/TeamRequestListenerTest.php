<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Team\Unit\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamRequest;
use App\BoundedContext\VideoGamesRecords\Team\Domain\ValueObject\TeamRequestStatus;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\EventListener\TeamRequestListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;

class TeamRequestListenerTest extends TestCase
{
    public function testPostUpdateSetsPlayerTeamWhenRequestIsAccepted(): void
    {
        $team = $this->createMock(Team::class);

        $player = $this->createMock(Player::class);
        $player->expects($this->once())->method('setTeam')->with($team);

        $teamRequest = $this->createMock(TeamRequest::class);
        $teamRequest->method('getTeamRequestStatus')->willReturn(TeamRequestStatus::ACCEPTED);
        $teamRequest->method('getPlayer')->willReturn($player);
        $teamRequest->method('getTeam')->willReturn($team);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($em);

        $listener = new TeamRequestListener();
        $listener->postUpdate($teamRequest, $lifecycleArgs);
    }

    public function testPostUpdateDoesNotSetTeamWhenRequestIsActive(): void
    {
        $player = $this->createMock(Player::class);
        $player->expects($this->never())->method('setTeam');

        $teamRequest = $this->createMock(TeamRequest::class);
        $teamRequest->method('getTeamRequestStatus')->willReturn(TeamRequestStatus::ACTIVE);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($em);

        $listener = new TeamRequestListener();
        $listener->postUpdate($teamRequest, $lifecycleArgs);
    }

    public function testPostUpdateDoesNotSetTeamWhenRequestIsRefused(): void
    {
        $player = $this->createMock(Player::class);
        $player->expects($this->never())->method('setTeam');

        $teamRequest = $this->createMock(TeamRequest::class);
        $teamRequest->method('getTeamRequestStatus')->willReturn(TeamRequestStatus::REFUSED);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($em);

        $listener = new TeamRequestListener();
        $listener->postUpdate($teamRequest, $lifecycleArgs);
    }
}
