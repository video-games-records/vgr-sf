<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Repository;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Entity\PlatformConnection;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;

interface PlatformConnectionRepositoryInterface
{
    public function findByPlayerAndPlatform(Player $player, PlatformEnum $platform): ?PlatformConnection;

    /** @return list<PlatformConnection> */
    public function findByPlayer(Player $player): array;

    public function save(PlatformConnection $connection): void;

    public function remove(PlatformConnection $connection): void;
}
