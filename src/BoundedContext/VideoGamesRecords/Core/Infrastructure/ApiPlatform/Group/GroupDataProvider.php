<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Group;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Group\GroupDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\GroupMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GroupRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<GroupDTO> */
class GroupDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly GroupMapper $groupMapper
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?GroupDTO
    {
        $id = $uriVariables['id'] ?? null;

        if ($id === null) {
            return null;
        }

        $group = $this->groupRepository->find((int) $id);

        if ($group === null) {
            throw new NotFoundHttpException('Group not found');
        }

        return $this->groupMapper->toDTO($group);
    }
}
