<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\GamingPlatform\Unit\Application\Service;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Application\Service\PlatformProfileService;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Application\Service\PlatformProviderRegistry;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Entity\PlatformConnection;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Provider\PlatformProviderInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Repository\PlatformConnectionRepositoryInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformProfileData;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class PlatformProfileServiceTest extends TestCase
{
    private function makeService(
        PlatformConnectionRepositoryInterface $repo,
        PlatformProviderRegistry $registry,
        CacheItemPoolInterface $cache,
    ): PlatformProfileService {
        return new PlatformProfileService($repo, $registry, $cache);
    }

    public function testGetProfilesReturnsEmptyWhenNoConnections(): void
    {
        $repo = $this->createMock(PlatformConnectionRepositoryInterface::class);
        $repo->method('findByPlayer')->willReturn([]);

        $registry = $this->createMock(PlatformProviderRegistry::class);
        $cache = $this->createMock(CacheItemPoolInterface::class);

        $result = $this->makeService($repo, $registry, $cache)
            ->getProfilesForPlayer($this->createMock(Player::class));

        $this->assertSame([], $result);
    }

    public function testGetProfilesReturnsCachedDataOnCacheHit(): void
    {
        $profileData = new PlatformProfileData(externalId: '123', username: 'steamuser');

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(true);
        $cacheItem->method('get')->willReturn($profileData);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn($cacheItem);

        $connection = $this->createMock(PlatformConnection::class);
        $connection->method('getPlatform')->willReturn(PlatformEnum::STEAM);
        $connection->method('getExternalId')->willReturn('123');

        $repo = $this->createMock(PlatformConnectionRepositoryInterface::class);
        $repo->method('findByPlayer')->willReturn([$connection]);

        $registry = $this->createMock(PlatformProviderRegistry::class);
        $registry->expects($this->never())->method('get');

        $result = $this->makeService($repo, $registry, $cache)
            ->getProfilesForPlayer($this->createMock(Player::class));

        $this->assertArrayHasKey('steam', $result);
        $this->assertSame($profileData, $result['steam']);
    }

    public function testGetProfilesFetchesFromProviderOnCacheMiss(): void
    {
        $profileData = new PlatformProfileData(externalId: '123', username: 'steamuser');

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(false);
        $cacheItem->method('set')->willReturnSelf();
        $cacheItem->method('expiresAfter')->willReturnSelf();

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn($cacheItem);
        $cache->expects($this->once())->method('save');

        $provider = $this->createMock(PlatformProviderInterface::class);
        $provider->method('fetchProfile')->with('123')->willReturn($profileData);

        $registry = $this->createMock(PlatformProviderRegistry::class);
        $registry->method('get')->willReturn($provider);

        $connection = $this->createMock(PlatformConnection::class);
        $connection->method('getPlatform')->willReturn(PlatformEnum::STEAM);
        $connection->method('getExternalId')->willReturn('123');

        $repo = $this->createMock(PlatformConnectionRepositoryInterface::class);
        $repo->method('findByPlayer')->willReturn([$connection]);

        $result = $this->makeService($repo, $registry, $cache)
            ->getProfilesForPlayer($this->createMock(Player::class));

        $this->assertArrayHasKey('steam', $result);
        $this->assertSame($profileData, $result['steam']);
    }

    public function testGetProfilesSkipsPlatformWhenProviderThrows(): void
    {
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(false);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn($cacheItem);
        $cache->expects($this->never())->method('save');

        $provider = $this->createMock(PlatformProviderInterface::class);
        $provider->method('fetchProfile')->willThrowException(new \RuntimeException('API down'));

        $registry = $this->createMock(PlatformProviderRegistry::class);
        $registry->method('get')->willReturn($provider);

        $connection = $this->createMock(PlatformConnection::class);
        $connection->method('getPlatform')->willReturn(PlatformEnum::STEAM);
        $connection->method('getExternalId')->willReturn('123');

        $repo = $this->createMock(PlatformConnectionRepositoryInterface::class);
        $repo->method('findByPlayer')->willReturn([$connection]);

        $result = $this->makeService($repo, $registry, $cache)
            ->getProfilesForPlayer($this->createMock(Player::class));

        $this->assertSame([], $result);
    }

    public function testGetProfilesKeyedByPlatformValue(): void
    {
        $steam = new PlatformProfileData(externalId: 'sid', username: 'SteamUser');
        $retro = new PlatformProfileData(externalId: 'ruser', username: 'RetroUser');

        $makeHitItem = function (PlatformProfileData $data): CacheItemInterface {
            $item = $this->createMock(CacheItemInterface::class);
            $item->method('isHit')->willReturn(true);
            $item->method('get')->willReturn($data);
            return $item;
        };

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')
            ->willReturnOnConsecutiveCalls($makeHitItem($steam), $makeHitItem($retro));

        $conn1 = $this->createMock(PlatformConnection::class);
        $conn1->method('getPlatform')->willReturn(PlatformEnum::STEAM);
        $conn1->method('getExternalId')->willReturn('sid');

        $conn2 = $this->createMock(PlatformConnection::class);
        $conn2->method('getPlatform')->willReturn(PlatformEnum::RETRO_ACHIEVEMENTS);
        $conn2->method('getExternalId')->willReturn('ruser');

        $repo = $this->createMock(PlatformConnectionRepositoryInterface::class);
        $repo->method('findByPlayer')->willReturn([$conn1, $conn2]);

        $result = $this->makeService($repo, $this->createMock(PlatformProviderRegistry::class), $cache)
            ->getProfilesForPlayer($this->createMock(Player::class));

        $this->assertArrayHasKey('steam', $result);
        $this->assertArrayHasKey('retro_achievements', $result);
    }
}
