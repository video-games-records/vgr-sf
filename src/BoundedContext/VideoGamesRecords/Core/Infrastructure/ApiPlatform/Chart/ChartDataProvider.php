<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Chart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart\ChartDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\ChartMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\ChartRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<ChartDTO> */
class ChartDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly ChartRepository $chartRepository,
        private readonly ChartMapper $chartMapper
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?ChartDTO
    {
        $id = $uriVariables['id'] ?? null;

        if ($id === null) {
            return null;
        }

        $chart = $this->chartRepository->find((int) $id);

        if ($chart === null) {
            throw new NotFoundHttpException('Chart not found');
        }

        return $this->chartMapper->toDTO($chart);
    }
}
