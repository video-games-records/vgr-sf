<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerGroupRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank0Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank1Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank2Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank3Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank4Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank5Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\LastUpdateTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbChartProvenTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankMedalTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointChartTrait;

#[ORM\Table(name:'vgr_player_group')]
#[ORM\Entity(repositoryClass: PlayerGroupRepository::class)]
class PlayerGroup
{
    use NbChartTrait;
    use NbChartProvenTrait;
    use RankMedalTrait;
    use ChartRank0Trait;
    use ChartRank1Trait;
    use ChartRank2Trait;
    use ChartRank3Trait;
    use ChartRank4Trait;
    use ChartRank5Trait;
    use RankPointChartTrait;
    use PointChartTrait;
    use LastUpdateTrait;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name:'player_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Player $player;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Group::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name:'group_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Group $group;

    public function setGroup(Group $group): void
    {
        $this->group = $group;
    }

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getId(): string
    {
        return sprintf('player=%d;group=%d', $this->player->getId(), $this->group->getId());
    }
}
