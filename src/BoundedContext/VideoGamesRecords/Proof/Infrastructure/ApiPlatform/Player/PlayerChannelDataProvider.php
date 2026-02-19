<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\ApiPlatform\Player;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Proof\Application\DTO\Player\PlayerChannelDTO;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository\VideoRepository;

/** @implements ProviderInterface<PlayerChannelDTO> */
class PlayerChannelDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly VideoRepository $videoRepository,
    ) {
    }

    /**
     * @return array<PlayerChannelDTO>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $players = $this->videoRepository->findPlayersWithMinVideos(5);

        return array_map(
            fn (array $row) => new PlayerChannelDTO(
                id: $row['id'],
                pseudo: $row['pseudo'],
                slug: $row['slug'],
                nbVideo: (int) $row['nbVideo'],
            ),
            $players
        );
    }
}
