<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Game;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Group\GroupDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\GroupMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GroupRepository;

/** @implements ProviderInterface<GroupDTO> */
class GameGroupDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly GroupMapper $groupMapper
    ) {
    }

    /**
     * @return array<GroupDTO>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $id = $uriVariables['id'] ?? null;

        if ($id === null) {
            return [];
        }

        $groups = $this->groupRepository->findByGameId((int) $id);

        return array_map(
            fn ($group) => $this->groupMapper->toDTO($group),
            $groups
        );
    }
}
