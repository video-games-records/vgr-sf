<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Application\Service;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Repository\PlatformConnectionRepositoryInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;

readonly class UnlinkPlatformService
{
    public function __construct(
        private PlatformConnectionRepositoryInterface $repository,
    ) {
    }

    public function unlink(Player $player, PlatformEnum $platform): void
    {
        $connection = $this->repository->findByPlayerAndPlatform($player, $platform);

        if ($connection !== null) {
            $this->repository->remove($connection);
        }
    }
}
