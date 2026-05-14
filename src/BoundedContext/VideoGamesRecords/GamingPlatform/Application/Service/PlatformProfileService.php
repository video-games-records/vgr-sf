<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Application\Service;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Repository\PlatformConnectionRepositoryInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformProfileData;
use Psr\Cache\CacheItemPoolInterface;

class PlatformProfileService
{
    private const int CACHE_TTL = 3600;

    public function __construct(
        private readonly PlatformConnectionRepositoryInterface $repository,
        private readonly PlatformProviderRegistry $providerRegistry,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    /**
     * Returns platform profile data for each connected platform, keyed by platform value.
     * Results are cached for 1 hour. Failures degrade gracefully (platform is skipped).
     *
     * @return array<string, PlatformProfileData>
     */
    public function getProfilesForPlayer(Player $player): array
    {
        $profiles = [];

        foreach ($this->repository->findByPlayer($player) as $connection) {
            $platform = $connection->getPlatform();
            $cacheKey = 'platform_profile_' . $platform->value . '_' . $connection->getExternalId();

            $item = $this->cache->getItem($cacheKey);

            if ($item->isHit()) {
                /** @var PlatformProfileData $data */
                $data = $item->get();
                $profiles[$platform->value] = $data;
                continue;
            }

            try {
                $provider = $this->providerRegistry->get($platform);
                $data = $provider->fetchProfile($connection->getExternalId());

                $item->set($data);
                $item->expiresAfter(self::CACHE_TTL);
                $this->cache->save($item);

                $profiles[$platform->value] = $data;
            } catch (\Exception) {
                // API unavailable — skip this platform silently
            }
        }

        return $profiles;
    }
}
