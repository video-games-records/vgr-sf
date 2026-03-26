<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Player;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\PlayerGame\PlayerGameDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\PlayerGameMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerGameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<PlayerGameDTO> */
class PlayerGamesDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly PlayerGameRepository $playerGameRepository,
        private readonly PlayerGameMapper $playerGameMapper,
    ) {
    }

    /**
     * @return array<PlayerGameDTO>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $id = $uriVariables['id'] ?? null;

        if ($id === null) {
            return [];
        }

        $player = $this->playerRepository->find((int) $id);

        if ($player === null) {
            throw new NotFoundHttpException('Player not found');
        }

        $playerGames = $this->playerGameRepository->findAllByPlayerOrderedByLastUpdate($player);

        return array_map(
            fn ($playerGame) => $this->playerGameMapper->toDTO($playerGame),
            $playerGames
        );
    }
}
