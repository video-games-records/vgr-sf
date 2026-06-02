<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\GamingPlatform\Unit\Application\Service;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Application\Service\UnlinkPlatformService;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Entity\PlatformConnection;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Repository\PlatformConnectionRepositoryInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class UnlinkPlatformServiceTest extends TestCase
{
    private PlatformConnectionRepositoryInterface&MockObject $repository;
    private UnlinkPlatformService $service;
    private Player $player;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PlatformConnectionRepositoryInterface::class);
        $this->service = new UnlinkPlatformService($this->repository);
        $this->player = new Player();
    }

    public function testUnlinkRemovesExistingConnection(): void
    {
        $connection = new PlatformConnection($this->player, PlatformEnum::STEAM, 'ext_id', 'user');
        $this->repository->method('findByPlayerAndPlatform')->willReturn($connection);
        $this->repository->expects($this->once())->method('remove')->with($connection);

        $this->service->unlink($this->player, PlatformEnum::STEAM);
    }

    public function testUnlinkDoesNothingWhenConnectionNotFound(): void
    {
        $this->repository->method('findByPlayerAndPlatform')->willReturn(null);
        $this->repository->expects($this->never())->method('remove');

        $this->service->unlink($this->player, PlatformEnum::STEAM);
    }
}
