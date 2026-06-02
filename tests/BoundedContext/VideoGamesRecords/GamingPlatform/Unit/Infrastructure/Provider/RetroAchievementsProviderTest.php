<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\GamingPlatform\Unit\Infrastructure\Provider;

use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Infrastructure\Provider\RetroAchievementsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RetroAchievementsProviderTest extends TestCase
{
    private function makeProvider(): RetroAchievementsProvider
    {
        return new RetroAchievementsProvider(
            $this->createMock(HttpClientInterface::class),
            'test-api-key'
        );
    }

    public function testSupportsRetroAchievements(): void
    {
        $this->assertTrue($this->makeProvider()->supports(PlatformEnum::RETRO_ACHIEVEMENTS));
    }

    public function testDoesNotSupportOtherPlatforms(): void
    {
        $provider = $this->makeProvider();
        $this->assertFalse($provider->supports(PlatformEnum::STEAM));
        $this->assertFalse($provider->supports(PlatformEnum::XBOX));
        $this->assertFalse($provider->supports(PlatformEnum::PSN));
    }

    public function testGetAuthUrlThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $this->makeProvider()->getAuthUrl('https://example.com/callback');
    }

    public function testHandleCallbackThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $this->makeProvider()->handleCallback(new Request());
    }
}
