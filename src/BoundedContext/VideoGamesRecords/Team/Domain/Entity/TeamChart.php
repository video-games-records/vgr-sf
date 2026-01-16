<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamChartRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank0Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank1Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank2Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank3Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointChartTrait;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;

#[ORM\Table(name:'vgr_team_chart')]
#[ORM\Entity(repositoryClass: TeamChartRepository::class)]
class TeamChart
{
    use RankPointChartTrait;
    use PointChartTrait;
    //use RankMedalTrait;
    use ChartRank0Trait;
    use ChartRank1Trait;
    use ChartRank2Trait;
    use ChartRank3Trait;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name:'team_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Team $team;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Chart::class)]
    #[ORM\JoinColumn(name:'chart_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Chart $chart;


    public function setChart(Chart $chart): void
    {
        $this->chart = $chart;
    }

    public function getChart(): Chart
    {
        return $this->chart;
    }

    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }
}
