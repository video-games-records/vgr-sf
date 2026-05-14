<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Application\Service;

use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Provider\PlatformProviderInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;

class PlatformProviderRegistry
{
    /** @param iterable<PlatformProviderInterface> $providers */
    public function __construct(private readonly iterable $providers)
    {
    }

    public function get(PlatformEnum $platform): PlatformProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($platform)) {
                return $provider;
            }
        }

        throw new \RuntimeException(sprintf('No provider registered for platform "%s".', $platform->value));
    }
}
