<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Provider;

use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformIdentity;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformProfileData;
use Symfony\Component\HttpFoundation\Request;

interface PlatformProviderInterface
{
    public function getAuthUrl(string $callbackUrl): string;

    /**
     * @throws \RuntimeException when the callback is invalid or verification fails
     */
    public function handleCallback(Request $request): PlatformIdentity;

    public function fetchProfile(string $externalId): PlatformProfileData;

    public function supports(PlatformEnum $platform): bool;
}
