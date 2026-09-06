<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\MessageHandler\Group;

use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Group\CopyGroup;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GroupRepository;
use App\SharedKernel\Domain\Exception\EntityNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CopyGroupHandler
{
    public function __construct(
        private GroupRepository $groupRepository,
    ) {
    }

    public function __invoke(CopyGroup $copyGroup): void
    {
        /** @var Group|null $group */
        $group = $this->groupRepository->find($copyGroup->getGroupId());
        if (null === $group) {
            throw new EntityNotFoundException('Group', $copyGroup->getGroupId());
        }

        $this->groupRepository->copy($group, $copyGroup->isWithLibs());
    }
}
