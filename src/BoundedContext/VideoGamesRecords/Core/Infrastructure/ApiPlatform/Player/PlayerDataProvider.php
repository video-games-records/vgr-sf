<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Player;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Player\PlayerDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\PlayerMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PlayerDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly PlayerMapper $playerMapper
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PlayerDTO
    {
        $player = $this->playerRepository->find($uriVariables['id']);

        if (!$player) {
            throw new NotFoundHttpException('Player not found');
        }

        return $this->playerMapper->toDTO($player);
    }
}
