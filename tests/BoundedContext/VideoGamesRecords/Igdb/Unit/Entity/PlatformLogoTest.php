<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Igdb\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\PlatformLogo;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PlatformLogoTest extends TestCase
{
    private PlatformLogo $platformLogo;

    protected function setUp(): void
    {
        $this->platformLogo = new PlatformLogo();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesTimestamps(): void
    {
        $before = new DateTimeImmutable();
        $platformLogo = new PlatformLogo();
        $after = new DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $platformLogo->getCreatedAt());
        $this->assertLessThanOrEqual($after, $platformLogo->getCreatedAt());
        $this->assertGreaterThanOrEqual($before, $platformLogo->getUpdatedAt());
        $this->assertLessThanOrEqual($after, $platformLogo->getUpdatedAt());
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $result = $this->platformLogo->setId(15);
        $this->assertSame(15, $this->platformLogo->getId());
        $this->assertSame($this->platformLogo, $result);
    }

    public function testSetAndIsAlphaChannel(): void
    {
        $result = $this->platformLogo->setAlphaChannel(true);
        $this->assertTrue($this->platformLogo->isAlphaChannel());
        $this->assertSame($this->platformLogo, $result);
    }

    public function testSetAlphaChannelToFalse(): void
    {
        $this->platformLogo->setAlphaChannel(true);
        $this->platformLogo->setAlphaChannel(false);
        $this->assertFalse($this->platformLogo->isAlphaChannel());
    }

    public function testSetAndIsAnimated(): void
    {
        $result = $this->platformLogo->setAnimated(true);
        $this->assertTrue($this->platformLogo->isAnimated());
        $this->assertSame($this->platformLogo, $result);
    }

    public function testSetAnimatedToFalse(): void
    {
        $this->platformLogo->setAnimated(true);
        $this->platformLogo->setAnimated(false);
        $this->assertFalse($this->platformLogo->isAnimated());
    }

    public function testChecksumDefaultsToNull(): void
    {
        $this->assertNull($this->platformLogo->getChecksum());
    }

    public function testSetAndGetChecksum(): void
    {
        $result = $this->platformLogo->setChecksum('xyz-789');
        $this->assertSame('xyz-789', $this->platformLogo->getChecksum());
        $this->assertSame($this->platformLogo, $result);
    }

    public function testSetChecksumToNull(): void
    {
        $this->platformLogo->setChecksum('xyz-789');
        $this->platformLogo->setChecksum(null);
        $this->assertNull($this->platformLogo->getChecksum());
    }

    public function testSetAndGetHeight(): void
    {
        $result = $this->platformLogo->setHeight(480);
        $this->assertSame(480, $this->platformLogo->getHeight());
        $this->assertSame($this->platformLogo, $result);
    }

    public function testSetAndGetImageId(): void
    {
        $result = $this->platformLogo->setImageId('pl_abc123');
        $this->assertSame('pl_abc123', $this->platformLogo->getImageId());
        $this->assertSame($this->platformLogo, $result);
    }

    public function testUrlDefaultsToNull(): void
    {
        $this->assertNull($this->platformLogo->getUrl());
    }

    public function testSetAndGetUrl(): void
    {
        $result = $this->platformLogo->setUrl('https://images.igdb.com/igdb/image/upload/t_logo_med/pl_abc123.png');
        $this->assertSame(
            'https://images.igdb.com/igdb/image/upload/t_logo_med/pl_abc123.png',
            $this->platformLogo->getUrl()
        );
        $this->assertSame($this->platformLogo, $result);
    }

    public function testSetUrlToNull(): void
    {
        $this->platformLogo->setUrl('https://images.igdb.com/some-image.png');
        $this->platformLogo->setUrl(null);
        $this->assertNull($this->platformLogo->getUrl());
    }

    public function testSetAndGetWidth(): void
    {
        $result = $this->platformLogo->setWidth(640);
        $this->assertSame(640, $this->platformLogo->getWidth());
        $this->assertSame($this->platformLogo, $result);
    }

    // ------------------------------------------------------------------
    // Timestamps
    // ------------------------------------------------------------------

    public function testSetAndGetCreatedAt(): void
    {
        $date = new DateTimeImmutable('2024-01-15 10:00:00');
        $result = $this->platformLogo->setCreatedAt($date);
        $this->assertSame($date, $this->platformLogo->getCreatedAt());
        $this->assertSame($this->platformLogo, $result);
    }

    public function testSetAndGetUpdatedAt(): void
    {
        $date = new DateTimeImmutable('2024-06-01 12:00:00');
        $result = $this->platformLogo->setUpdatedAt($date);
        $this->assertSame($date, $this->platformLogo->getUpdatedAt());
        $this->assertSame($this->platformLogo, $result);
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testGetImageUrlWithDefaultSize(): void
    {
        $this->platformLogo->setImageId('pl_abc123');

        $url = $this->platformLogo->getImageUrl();

        $this->assertSame('https://images.igdb.com/igdb/image/upload/t_logo_med/pl_abc123.png', $url);
    }

    public function testGetImageUrlWithCustomSize(): void
    {
        $this->platformLogo->setImageId('pl_abc123');

        $url = $this->platformLogo->getImageUrl('thumb');

        $this->assertSame('https://images.igdb.com/igdb/image/upload/t_thumb/pl_abc123.png', $url);
    }

    public function testGetImageUrlWithCoverBigSize(): void
    {
        $this->platformLogo->setImageId('pl_xyz999');

        $url = $this->platformLogo->getImageUrl('cover_big');

        $this->assertSame('https://images.igdb.com/igdb/image/upload/t_cover_big/pl_xyz999.png', $url);
    }

    public function testToString(): void
    {
        $this->platformLogo->setImageId('pl_abc123');
        $this->platformLogo->setWidth(640);
        $this->platformLogo->setHeight(480);

        $this->assertSame('pl_abc123 (640x480)', (string) $this->platformLogo);
    }
}
