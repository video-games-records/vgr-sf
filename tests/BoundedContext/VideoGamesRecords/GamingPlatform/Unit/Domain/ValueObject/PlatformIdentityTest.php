<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\GamingPlatform\Unit\Domain\ValueObject;

use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformIdentity;
use PHPUnit\Framework\TestCase;

class PlatformIdentityTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $identity = new PlatformIdentity('ext-123', 'PlayerName', 'token-abc');

        $this->assertSame('ext-123', $identity->externalId);
        $this->assertSame('PlayerName', $identity->username);
        $this->assertSame('token-abc', $identity->tokenData);
    }

    public function testTokenDataDefaultsToNull(): void
    {
        $identity = new PlatformIdentity('ext-456', 'AnotherPlayer');

        $this->assertNull($identity->tokenData);
    }

    public function testUsernameCanBeNull(): void
    {
        $identity = new PlatformIdentity('ext-789', null);

        $this->assertNull($identity->username);
    }

    public function testExternalIdIsRequired(): void
    {
        $identity = new PlatformIdentity('my-id', null, null);

        $this->assertSame('my-id', $identity->externalId);
    }
}
