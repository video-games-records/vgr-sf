<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Application\Service;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Entity\PlatformConnection;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Repository\PlatformConnectionRepositoryInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformIdentity;

readonly class LinkPlatformService
{
    public function __construct(
        private PlatformConnectionRepositoryInterface $repository,
    ) {
    }

    public function link(Player $player, PlatformEnum $platform, PlatformIdentity $identity): PlatformConnection
    {
        $existing = $this->repository->findByPlayerAndPlatform($player, $platform);

        if ($existing !== null) {
            $existing->updateUsername($identity->username);
            $this->repository->save($existing);

            return $existing;
        }

        $connection = new PlatformConnection(
            player: $player,
            platform: $platform,
            externalId: $identity->externalId,
            username: $identity->username,
        );

        $this->repository->save($connection);

        return $connection;
    }
}
