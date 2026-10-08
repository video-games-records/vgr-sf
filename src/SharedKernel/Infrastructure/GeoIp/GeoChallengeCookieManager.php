<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\GeoIp;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

final class GeoChallengeCookieManager
{
    public function __construct(
        #[Autowire('%kernel.secret%')]
        private readonly string $appSecret,
        #[Autowire('%app.geoip.challenge_cookie_name%')]
        private readonly string $cookieName,
        #[Autowire('%app.geoip.challenge_cookie_ttl%')]
        private readonly int $cookieTtl,
    ) {
    }

    public function isPassed(Request $request): bool
    {
        $value = $request->cookies->get($this->cookieName);

        return null !== $value && hash_equals($this->expectedValue(), $value);
    }

    public function createCookie(): Cookie
    {
        return Cookie::create($this->cookieName)
            ->withValue($this->expectedValue())
            ->withExpires(time() + $this->cookieTtl)
            ->withHttpOnly(true)
            ->withSecure(true)
            ->withSameSite(Cookie::SAMESITE_LAX);
    }

    private function expectedValue(): string
    {
        return hash_hmac('sha256', 'geo_challenge_passed', $this->appSecret);
    }
}
