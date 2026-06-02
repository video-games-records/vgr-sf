<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\GamingPlatform\Unit\Infrastructure\Provider;

use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Infrastructure\Provider\SteamProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SteamProviderTest extends TestCase
{
    private function makeProvider(): SteamProvider
    {
        return new SteamProvider(
            $this->createMock(HttpClientInterface::class),
            'test-api-key'
        );
    }

    public function testSupportsSteam(): void
    {
        $this->assertTrue($this->makeProvider()->supports(PlatformEnum::STEAM));
    }

    public function testDoesNotSupportOtherPlatforms(): void
    {
        $provider = $this->makeProvider();
        $this->assertFalse($provider->supports(PlatformEnum::XBOX));
        $this->assertFalse($provider->supports(PlatformEnum::RETRO_ACHIEVEMENTS));
        $this->assertFalse($provider->supports(PlatformEnum::PSN));
    }

    public function testGetAuthUrlContainsSteamOpenIdEndpoint(): void
    {
        $url = $this->makeProvider()->getAuthUrl('https://example.com/callback');

        $this->assertStringContainsString('steamcommunity.com/openid/login', $url);
    }

    public function testGetAuthUrlContainsCallbackUrl(): void
    {
        $callbackUrl = 'https://example.com/callback';
        $url = $this->makeProvider()->getAuthUrl($callbackUrl);

        $this->assertStringContainsString(urlencode($callbackUrl), $url);
    }

    public function testGetAuthUrlContainsOpenIdMode(): void
    {
        $url = $this->makeProvider()->getAuthUrl('https://example.com/callback');

        $this->assertStringContainsString('checkid_setup', $url);
    }
}
