<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Group\GroupDTO;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;

class GroupMapper
{
    public function toDTO(Group $group): GroupDTO
    {
        $game = [
            'id' => (int) $group->getGame()->getId(),
            'name' => $group->getGame()->getName(),
            'slug' => $group->getGame()->getSlug()
        ];

        return new GroupDTO(
            id: (int) $group->getId(),
            name: $group->getName() ?? '',
            nbChart: $group->getNbChart(),
            nbPost: $group->getNbPost(),
            nbPlayer: $group->getNbPlayer(),
            isRank: $group->getIsRank(),
            isDlc: $group->getIsDlc(),
            slug: $group->getSlug(),
            game: $game
        );
    }
}
