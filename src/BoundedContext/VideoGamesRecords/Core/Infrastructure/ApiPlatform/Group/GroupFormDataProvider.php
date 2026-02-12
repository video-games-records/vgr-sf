<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Group;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart\ChartFormDataDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\ChartFormDataMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\ChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GroupRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<ChartFormDataDTO> */
class GroupFormDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly ChartRepository $chartRepository,
        private readonly UserProvider $userProvider,
        private readonly ChartFormDataMapper $chartFormDataMapper,
        private readonly RequestStack $requestStack,
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

        $group = $this->groupRepository->find((int) $id);

        if ($group === null) {
            throw new NotFoundHttpException('Group not found');
        }

        $player = $this->userProvider->getPlayer();

        if ($player === null) {
            throw new AccessDeniedHttpException();
        }

        $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'en';
        $page = $this->requestStack->getCurrentRequest()?->query->getInt('page', 1) ?? 1;
        $itemsPerPage = $this->requestStack->getCurrentRequest()?->query->getInt('itemsPerPage', 20) ?? 20;

        $paginator = $this->chartRepository->getList($player, $page, ['group' => $group], $locale, $itemsPerPage);

        $result = [];
        foreach ($paginator as $chart) {
            $playerChart = null;
            foreach ($chart->getPlayerCharts() as $pc) {
                if ($pc->getPlayer()->getId() === $player->getId()) {
                    $playerChart = $pc;
                    break;
                }
            }

            $result[] = $this->chartFormDataMapper->toDTO($chart, $playerChart);
        }

        return $result;
    }
}
