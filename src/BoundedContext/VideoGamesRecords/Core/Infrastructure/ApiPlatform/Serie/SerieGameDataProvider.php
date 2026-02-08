<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Serie;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Game\GameDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\GameMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;

/** @implements ProviderInterface<GameDTO> */
class SerieGameDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly GameMapper $gameMapper
    ) {
    }

    /**
     * @return array<GameDTO>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $id = $uriVariables['id'] ?? null;

        if ($id === null) {
            return [];
        }

        $games = $this->gameRepository->findBySerieOrderedByNbPost((int) $id);

        return array_map(
            fn ($game) => $this->gameMapper->toDTO($game),
            $games
        );
    }
}
