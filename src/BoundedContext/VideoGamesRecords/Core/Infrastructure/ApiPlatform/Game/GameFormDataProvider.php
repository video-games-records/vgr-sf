<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Game;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Chart\ChartFormDataDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\ChartFormDataMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\ChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<ChartFormDataDTO> */
class GameFormDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly GameRepository $gameRepository,
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

        $game = $this->gameRepository->find((int) $id);

        if ($game === null) {
            throw new NotFoundHttpException('Game not found');
        }

        $player = $this->userProvider->getPlayer();

        if ($player === null) {
            throw new AccessDeniedHttpException();
        }

        $request = $this->requestStack->getCurrentRequest();
        $locale = $request?->getLocale() ?? 'en';
        $page = $request?->query->getInt('page', 1) ?? 1;
        $itemsPerPage = $request?->query->getInt('itemsPerPage', 20) ?? 20;

        $search = ['game' => $game];
        $searchTerm = $request?->query->get('search', '') ?? '';
        if ($searchTerm !== '') {
            $search['term'] = $searchTerm;
        }


        $paginator = $this->chartRepository->getList($player, $page, $search, $locale, $itemsPerPage);

        $result = [];
        foreach ($paginator as $chart) {
            $playerChart = null;
            foreach ($chart->getPlayerCharts() as $pc) {
                if ($pc->getPlayer()->getId() === $player->getId()) {
                    $playerChart = $pc;
                    break;
                }
            }

            $result[] = $this->chartFormDataMapper->toDTO($chart, $playerChart, true);
        }

        return $result;
    }
}
