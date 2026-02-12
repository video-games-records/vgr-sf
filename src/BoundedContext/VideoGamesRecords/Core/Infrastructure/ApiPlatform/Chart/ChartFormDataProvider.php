<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Chart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart\ChartFormDataDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\ChartFormDataMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\ChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<ChartFormDataDTO> */
class ChartFormDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly ChartRepository $chartRepository,
        private readonly UserProvider $userProvider,
        private readonly ChartFormDataMapper $chartFormDataMapper,
    ) {
    }

    /**
     * @return array<ChartFormDataDTO>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $id = $uriVariables['id'] ?? null;

        if ($id === null) {
            return [];
        }

        $chart = $this->chartRepository->find((int) $id);

        if ($chart === null) {
            throw new NotFoundHttpException('Chart not found');
        }

        $player = $this->userProvider->getPlayer();

        if ($player === null) {
            throw new AccessDeniedHttpException();
        }

        $paginator = $this->chartRepository->getList($player, 1, ['chart' => $chart], 'en', 1);

        $loadedChart = null;
        foreach ($paginator as $item) {
            $loadedChart = $item;
            break;
        }

        if ($loadedChart === null) {
            return [];
        }

        $playerChart = null;
        foreach ($loadedChart->getPlayerCharts() as $pc) {
            if ($pc->getPlayer()->getId() === $player->getId()) {
                $playerChart = $pc;
                break;
            }
        }

        return [$this->chartFormDataMapper->toDTO($loadedChart, $playerChart)];
    }
}
