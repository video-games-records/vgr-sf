<?php

declare(strict_types=1);

namespace App\Tests\SharedKernel\Unit\Infrastructure;

use App\SharedKernel\Infrastructure\TokenGenerator;
use PHPUnit\Framework\TestCase;

class TokenGeneratorTest extends TestCase
{
    private TokenGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new TokenGenerator();
    }

    public function testGenerateTokenIsNotEmpty(): void
    {
        $token = $this->generator->generateToken();
        $this->assertNotEmpty($token);
    }

    public function testGenerateTokenHasExpectedLength(): void
    {
        $token = $this->generator->generateToken();
        // base64_encode(32 bytes) = 44 chars, minus padding '=' = ~43 chars
        $this->assertGreaterThanOrEqual(40, strlen($token));
    }

    public function testGenerateTokenContainsOnlyUrlSafeChars(): void
    {
        $token = $this->generator->generateToken();
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9\-_]+$/', $token);
    }

    public function testGenerateTokenProducesUniqueValues(): void
    {
        $token1 = $this->generator->generateToken();
        $token2 = $this->generator->generateToken();
        $this->assertNotSame($token1, $token2);
    }

    public function testGenerateTokenHasNoPaddingChar(): void
    {
        $token = $this->generator->generateToken();
        $this->assertStringNotContainsString('=', $token);
    }
}
