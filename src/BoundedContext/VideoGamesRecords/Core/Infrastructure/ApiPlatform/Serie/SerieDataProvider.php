<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Serie;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Serie\SerieDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\SerieMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\SerieRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<SerieDTO> */
class SerieDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly SerieRepository $serieRepository,
        private readonly SerieMapper $serieMapper
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?SerieDTO
    {
        $id = $uriVariables['id'] ?? null;

        if ($id === null) {
            return null;
        }

        $serie = $this->serieRepository->find((int) $id);

        if ($serie === null) {
            throw new NotFoundHttpException('Serie not found');
        }

        return $this->serieMapper->toDTO($serie);
    }
}
