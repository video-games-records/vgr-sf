<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlatformBadge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class PlatformTest extends TestCase
{
    private Platform $platform;

    protected function setUp(): void
    {
        $this->platform = new Platform();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyGamesCollection(): void
    {
        $platform = new Platform();
        $this->assertInstanceOf(Collection::class, $platform->getGames());
        $this->assertCount(0, $platform->getGames());
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->platform->getId());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->platform->setId(1);
        $this->assertSame(1, $this->platform->getId());
        $this->assertSame($this->platform, $result);
    }

    public function testNameDefaultsToEmptyString(): void
    {
        $this->assertSame('', $this->platform->getName());
    }

    public function testSetAndGetName(): void
    {
        $result = $this->platform->setName('PlayStation 5');
        $this->assertSame('PlayStation 5', $this->platform->getName());
        $this->assertSame($this->platform, $result);
    }

    public function testPictureDefaultsToNull(): void
    {
        $this->assertNull($this->platform->getPicture());
    }

    public function testSetAndGetPicture(): void
    {
        $result = $this->platform->setPicture('ps5.jpg');
        $this->assertSame('ps5.jpg', $this->platform->getPicture());
        $this->assertSame($this->platform, $result);
    }

    public function testSetPictureToNull(): void
    {
        $this->platform->setPicture('ps5.jpg');
        $this->platform->setPicture(null);
        $this->assertNull($this->platform->getPicture());
    }

    public function testStatusDefaultsToInactif(): void
    {
        $this->assertSame('INACTIF', $this->platform->getStatus());
    }

    public function testSetAndGetStatus(): void
    {
        $result = $this->platform->setStatus('ACTIF');
        $this->assertSame('ACTIF', $this->platform->getStatus());
        $this->assertSame($this->platform, $result);
    }

    // ------------------------------------------------------------------
    // Badge relation
    // ------------------------------------------------------------------

    public function testBadgeDefaultsToNull(): void
    {
        $this->assertNull($this->platform->getBadge());
    }

    public function testSetAndGetBadge(): void
    {
        $badge = $this->createMock(PlatformBadge::class);
        $result = $this->platform->setBadge($badge);
        $this->assertSame($badge, $this->platform->getBadge());
        $this->assertSame($this->platform, $result);
    }

    public function testSetBadgeToNull(): void
    {
        $badge = $this->createMock(PlatformBadge::class);
        $this->platform->setBadge($badge);
        $this->platform->setBadge(null);
        $this->assertNull($this->platform->getBadge());
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->platform->setId(2);
        $this->platform->setName('Nintendo Switch');
        $this->assertSame('Nintendo Switch [2]', (string) $this->platform);
    }
}
