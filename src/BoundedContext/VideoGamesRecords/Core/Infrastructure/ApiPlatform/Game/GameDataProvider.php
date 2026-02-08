<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Game;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Game\GameDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\GameMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;

/** @implements ProviderInterface<GameDTO> */
class GameDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly GameMapper $gameMapper
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?GameDTO
    {
        $id = $uriVariables['id'] ?? null;

        if ($id === null) {
            return null;
        }

        $game = $this->gameRepository->find((int) $id);

        if ($game === null) {
            return null;
        }

        return $this->gameMapper->toDTO($game);
    }
}
