<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Group;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart\ChartDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\ChartMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\ChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GroupRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<ChartDTO> */
class GroupChartDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly ChartRepository $chartRepository,
        private readonly ChartMapper $chartMapper,
        private readonly RequestStack $requestStack
    ) {
    }

    /**
     * @return array<ChartDTO>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $id = $uriVariables['id'] ?? null;

        if ($id === null) {
            return [];
        }

        $group = $this->groupRepository->find((int) $id);

        if ($group === null) {
            throw new NotFoundHttpException('Group not found');
        }

        $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'en';

        $charts = $this->chartRepository->findByGroupId((int) $id, $group->getOrderBy(), $locale);

        return array_map(
            fn ($chart) => $this->chartMapper->toDTO($chart),
            $charts
        );
    }
}
