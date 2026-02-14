<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\PlayerSerie;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\PlayerSerie\PlayerSerieDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\PlayerSerieMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerSerieRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<PlayerSerieDTO> */
class PlayerSerieDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly PlayerSerieRepository $playerSerieRepository,
        private readonly PlayerSerieMapper $playerSerieMapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?PlayerSerieDTO
    {
        $playerId = $uriVariables['playerId'] ?? null;
        $serieId = $uriVariables['serieId'] ?? null;

        if ($playerId === null || $serieId === null) {
            throw new NotFoundHttpException('Player and Serie are required');
        }

        $playerSerie = $this->playerSerieRepository->findOneBy([
            'player' => (int) $playerId,
            'serie' => (int) $serieId,
        ]);

        if ($playerSerie === null) {
            return null;
        }

        return $this->playerSerieMapper->toDTO($playerSerie);
    }
}
