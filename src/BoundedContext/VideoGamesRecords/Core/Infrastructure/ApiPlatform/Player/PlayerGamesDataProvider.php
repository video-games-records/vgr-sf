<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Player;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
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
        private readonly Pagination $pagination,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $id = $uriVariables['id'] ?? null;

        if ($id === null) {
            return new TraversablePaginator(new \ArrayIterator([]), 1, 10, 0);
        }

        $player = $this->playerRepository->find((int) $id);

        if ($player === null) {
            throw new NotFoundHttpException('Player not found');
        }

        $page = $this->pagination->getPage($context);
        $limit = $this->pagination->getLimit($operation, $context);
        $offset = $this->pagination->getOffset($operation, $context);

        $total = $this->playerGameRepository->countByPlayer($player);
        $playerGames = $this->playerGameRepository->findAllByPlayerOrderedByLastUpdate($player, $limit, $offset);

        $dtos = array_map(
            fn ($playerGame) => $this->playerGameMapper->toDTO($playerGame),
            $playerGames
        );

        return new TraversablePaginator(new \ArrayIterator($dtos), $page, $limit, $total);
    }
}
