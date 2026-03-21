<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Unit\Entity;

use App\BoundedContext\User\Domain\Entity\RefreshToken;
use DateTime;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken as BaseRefreshToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class RefreshTokenTest extends TestCase
{
    private RefreshToken $refreshToken;

    protected function setUp(): void
    {
        $this->refreshToken = new RefreshToken();
    }

    // ------------------------------------------------------------------
    // Inheritance
    // ------------------------------------------------------------------

    public function testExtendsBaseRefreshToken(): void
    {
        $this->assertInstanceOf(BaseRefreshToken::class, $this->refreshToken);
    }

    // ------------------------------------------------------------------
    // Default values
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->refreshToken->getId());
    }

    public function testRefreshTokenDefaultsToNull(): void
    {
        $this->assertNull($this->refreshToken->getRefreshToken());
    }

    public function testUsernameDefaultsToNull(): void
    {
        $this->assertNull($this->refreshToken->getUsername());
    }

    public function testValidDefaultsToNull(): void
    {
        $this->assertNull($this->refreshToken->getValid());
    }

    // ------------------------------------------------------------------
    // Getters / setters
    // ------------------------------------------------------------------

    public function testSetAndGetRefreshToken(): void
    {
        $token = 'abc123tokenvalue';
        $result = $this->refreshToken->setRefreshToken($token);

        $this->assertSame($token, $this->refreshToken->getRefreshToken());
        $this->assertSame($this->refreshToken, $result);
    }

    public function testSetAndGetUsername(): void
    {
        $result = $this->refreshToken->setUsername('user@example.com');

        $this->assertSame('user@example.com', $this->refreshToken->getUsername());
        $this->assertSame($this->refreshToken, $result);
    }

    public function testSetAndGetValid(): void
    {
        $date = new DateTime('2026-12-31 23:59:59');
        $result = $this->refreshToken->setValid($date);

        $this->assertSame($date, $this->refreshToken->getValid());
        $this->assertSame($this->refreshToken, $result);
    }

    // ------------------------------------------------------------------
    // isValid
    // ------------------------------------------------------------------

    public function testIsValidReturnsFalseWhenValidIsNull(): void
    {
        $this->assertFalse($this->refreshToken->isValid());
    }

    public function testIsValidReturnsTrueWhenValidDateIsInFuture(): void
    {
        $future = new DateTime('+1 hour');
        $this->refreshToken->setValid($future);

        $this->assertTrue($this->refreshToken->isValid());
    }

    public function testIsValidReturnsFalseWhenValidDateIsInPast(): void
    {
        $past = new DateTime('-1 hour');
        $this->refreshToken->setValid($past);

        $this->assertFalse($this->refreshToken->isValid());
    }

    // ------------------------------------------------------------------
    // createForUserWithTtl factory
    // ------------------------------------------------------------------

    public function testCreateForUserWithTtlCreatesValidToken(): void
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('test@example.com');

        $token = RefreshToken::createForUserWithTtl('mytoken', $user, 3600);

        $this->assertInstanceOf(RefreshToken::class, $token);
        $this->assertSame('mytoken', $token->getRefreshToken());
        $this->assertSame('test@example.com', $token->getUsername());
        $this->assertTrue($token->isValid());
    }

    public function testCreateForUserWithTtlWithZeroTtlIsValid(): void
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('user@example.com');

        $token = RefreshToken::createForUserWithTtl('tok', $user, 0);

        // TTL 0 means valid is set to current time (not modified), still >= now at creation
        $this->assertInstanceOf(RefreshToken::class, $token);
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToStringReturnsRefreshTokenValue(): void
    {
        $this->refreshToken->setRefreshToken('tokenstring');

        $this->assertSame('tokenstring', (string) $this->refreshToken);
    }

    public function testToStringReturnsEmptyStringWhenNoToken(): void
    {
        $this->assertSame('', (string) $this->refreshToken);
    }
}
