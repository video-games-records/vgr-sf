<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\GamingPlatform\Unit\Application\Service;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Application\Service\LinkPlatformService;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Entity\PlatformConnection;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Repository\PlatformConnectionRepositoryInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformIdentity;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class LinkPlatformServiceTest extends TestCase
{
    private PlatformConnectionRepositoryInterface&MockObject $repository;
    private LinkPlatformService $service;
    private Player $player;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PlatformConnectionRepositoryInterface::class);
        $this->service = new LinkPlatformService($this->repository);
        $this->player = new Player();
    }

    public function testLinkCreatesNewConnectionWhenNoneExists(): void
    {
        $this->repository->method('findByPlayerAndPlatform')->willReturn(null);
        $this->repository->expects($this->once())->method('save');

        $identity = new PlatformIdentity('ext_id_123', 'gamer');
        $connection = $this->service->link($this->player, PlatformEnum::STEAM, $identity);

        $this->assertInstanceOf(PlatformConnection::class, $connection);
        $this->assertSame($this->player, $connection->getPlayer());
        $this->assertSame(PlatformEnum::STEAM, $connection->getPlatform());
        $this->assertSame('ext_id_123', $connection->getExternalId());
        $this->assertSame('gamer', $connection->getUsername());
    }

    public function testLinkUpdatesUsernameWhenConnectionAlreadyExists(): void
    {
        $existing = new PlatformConnection($this->player, PlatformEnum::STEAM, 'ext_id_123', 'old_name');
        $this->repository->method('findByPlayerAndPlatform')->willReturn($existing);
        $this->repository->expects($this->once())->method('save')->with($existing);

        $identity = new PlatformIdentity('ext_id_123', 'new_name');
        $connection = $this->service->link($this->player, PlatformEnum::STEAM, $identity);

        $this->assertSame($existing, $connection);
        $this->assertSame('new_name', $connection->getUsername());
    }

    public function testLinkSetsTokenDataWhenProvidedOnNew(): void
    {
        $this->repository->method('findByPlayerAndPlatform')->willReturn(null);

        $identity = new PlatformIdentity('ext_id', 'user', '{"token":"abc"}');
        $connection = $this->service->link($this->player, PlatformEnum::STEAM, $identity);

        $this->assertSame('{"token":"abc"}', $connection->getTokenData());
    }

    public function testLinkSetsTokenDataWhenProvidedOnExisting(): void
    {
        $existing = new PlatformConnection($this->player, PlatformEnum::STEAM, 'ext_id', 'user');
        $this->repository->method('findByPlayerAndPlatform')->willReturn($existing);

        $identity = new PlatformIdentity('ext_id', 'user', '{"token":"new"}');
        $connection = $this->service->link($this->player, PlatformEnum::STEAM, $identity);

        $this->assertSame('{"token":"new"}', $connection->getTokenData());
    }

    public function testLinkDoesNotUpdateTokenDataWhenNullOnExisting(): void
    {
        $existing = new PlatformConnection($this->player, PlatformEnum::STEAM, 'ext_id', 'user');
        $existing->setTokenData('{"token":"original"}');
        $this->repository->method('findByPlayerAndPlatform')->willReturn($existing);

        $identity = new PlatformIdentity('ext_id', 'user', null);
        $connection = $this->service->link($this->player, PlatformEnum::STEAM, $identity);

        $this->assertSame('{"token":"original"}', $connection->getTokenData());
    }
}
