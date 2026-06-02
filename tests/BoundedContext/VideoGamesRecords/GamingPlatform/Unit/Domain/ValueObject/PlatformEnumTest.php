<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\GamingPlatform\Unit\Domain\ValueObject;

use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PlatformEnumTest extends TestCase
{
    // ------------------------------------------------------------------
    // getLabel
    // ------------------------------------------------------------------

    /** @return array<string, array{PlatformEnum, string}> */
    public static function labelProvider(): array
    {
        return [
            'steam'               => [PlatformEnum::STEAM,              'Steam'],
            'xbox'                => [PlatformEnum::XBOX,               'Xbox Live'],
            'psn'                 => [PlatformEnum::PSN,                'PlayStation Network'],
            'epic'                => [PlatformEnum::EPIC,               'Epic Games'],
            'gog'                 => [PlatformEnum::GOG,                'GOG'],
            'battlenet'           => [PlatformEnum::BATTLENET,          'Battle.net'],
            'nintendo'            => [PlatformEnum::NINTENDO,           'Nintendo'],
            'retro_achievements'  => [PlatformEnum::RETRO_ACHIEVEMENTS, 'RetroAchievements'],
        ];
    }

    #[DataProvider('labelProvider')]
    public function testGetLabel(PlatformEnum $platform, string $expectedLabel): void
    {
        $this->assertSame($expectedLabel, $platform->getLabel());
    }

    // ------------------------------------------------------------------
    // isSupported
    // ------------------------------------------------------------------

    public function testSteamIsSupported(): void
    {
        $this->assertTrue(PlatformEnum::STEAM->isSupported());
    }

    public function testXboxIsSupported(): void
    {
        $this->assertTrue(PlatformEnum::XBOX->isSupported());
    }

    public function testRetroAchievementsIsSupported(): void
    {
        $this->assertTrue(PlatformEnum::RETRO_ACHIEVEMENTS->isSupported());
    }

    public function testPsnIsNotSupported(): void
    {
        $this->assertFalse(PlatformEnum::PSN->isSupported());
    }

    public function testEpicIsNotSupported(): void
    {
        $this->assertFalse(PlatformEnum::EPIC->isSupported());
    }

    public function testGogIsNotSupported(): void
    {
        $this->assertFalse(PlatformEnum::GOG->isSupported());
    }

    // ------------------------------------------------------------------
    // profileUrl
    // ------------------------------------------------------------------

    public function testSteamProfileUrl(): void
    {
        $url = PlatformEnum::STEAM->profileUrl('76561198012345678');
        $this->assertSame('https://steamcommunity.com/profiles/76561198012345678', $url);
    }

    public function testXboxProfileUrl(): void
    {
        $url = PlatformEnum::XBOX->profileUrl('GamerTag');
        $this->assertSame('https://www.xbox.com/play/user/GamerTag', $url);
    }

    public function testRetroAchievementsProfileUrl(): void
    {
        $url = PlatformEnum::RETRO_ACHIEVEMENTS->profileUrl('username');
        $this->assertSame('https://retroachievements.org/user/username', $url);
    }

    public function testPsnProfileUrl(): void
    {
        $url = PlatformEnum::PSN->profileUrl('psn_user');
        $this->assertSame('https://psnprofiles.com/psn_user', $url);
    }

    // ------------------------------------------------------------------
    // supported()
    // ------------------------------------------------------------------

    public function testSupportedReturnsOnlySupportedPlatforms(): void
    {
        $supported = PlatformEnum::supported();

        $this->assertNotEmpty($supported);
        foreach ($supported as $platform) {
            $this->assertTrue($platform->isSupported());
        }
    }

    public function testSupportedContainsExpectedPlatforms(): void
    {
        $supported = PlatformEnum::supported();
        $values = array_map(fn(PlatformEnum $p) => $p->value, $supported);

        $this->assertContains('steam', $values);
        $this->assertContains('xbox', $values);
        $this->assertContains('retro_achievements', $values);
        $this->assertNotContains('psn', $values);
    }
}
