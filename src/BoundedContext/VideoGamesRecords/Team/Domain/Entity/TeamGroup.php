<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamGroupRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank0Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank1Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank2Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank3Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankMedalTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointChartTrait;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;

#[ORM\Table(name:'vgr_team_group')]
#[ORM\Entity(repositoryClass: TeamGroupRepository::class)]
class TeamGroup
{
    use RankPointChartTrait;
    use PointChartTrait;
    use RankMedalTrait;
    use ChartRank0Trait;
    use ChartRank1Trait;
    use ChartRank2Trait;
    use ChartRank3Trait;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name:'team_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Team $team;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Group::class)]
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

    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    #[ApiProperty(identifier: true)]
    public function getId(): string
    {
        return sprintf('team=%d;group=%d', $this->team->getId(), $this->group->getId());
    }
}
