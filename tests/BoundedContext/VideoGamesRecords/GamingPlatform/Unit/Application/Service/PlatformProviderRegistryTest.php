<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\GamingPlatform\Unit\Application\Service;

use App\BoundedContext\VideoGamesRecords\GamingPlatform\Application\Service\PlatformProviderRegistry;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Provider\PlatformProviderInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class PlatformProviderRegistryTest extends TestCase
{
    private function makeProvider(PlatformEnum $supportedPlatform): PlatformProviderInterface&MockObject
    {
        $provider = $this->createMock(PlatformProviderInterface::class);
        $provider->method('supports')->willReturnCallback(
            fn(PlatformEnum $p) => $p === $supportedPlatform
        );
        return $provider;
    }

    public function testGetReturnsMatchingProvider(): void
    {
        $steamProvider = $this->makeProvider(PlatformEnum::STEAM);
        $registry = new PlatformProviderRegistry([$steamProvider]);

        $result = $registry->get(PlatformEnum::STEAM);
        $this->assertSame($steamProvider, $result);
    }

    public function testGetReturnsFirstMatchingProvider(): void
    {
        $steamProvider = $this->makeProvider(PlatformEnum::STEAM);
        $xboxProvider = $this->makeProvider(PlatformEnum::XBOX);
        $registry = new PlatformProviderRegistry([$steamProvider, $xboxProvider]);

        $this->assertSame($xboxProvider, $registry->get(PlatformEnum::XBOX));
        $this->assertSame($steamProvider, $registry->get(PlatformEnum::STEAM));
    }

    public function testGetThrowsRuntimeExceptionWhenNoProviderFound(): void
    {
        $steamProvider = $this->makeProvider(PlatformEnum::STEAM);
        $registry = new PlatformProviderRegistry([$steamProvider]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/xbox/i');

        $registry->get(PlatformEnum::XBOX);
    }

    public function testGetThrowsExceptionWhenRegistryIsEmpty(): void
    {
        $registry = new PlatformProviderRegistry([]);

        $this->expectException(\RuntimeException::class);
        $registry->get(PlatformEnum::STEAM);
    }
}
