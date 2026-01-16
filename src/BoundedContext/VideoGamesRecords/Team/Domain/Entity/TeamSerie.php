<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Domain\Entity;

use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamSerieRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank0Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank1Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank2Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank3Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbEqualTrait;
use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointGameTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankMedalTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointChartTrait;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;

#[ORM\Table(name:'vgr_team_serie')]
#[ORM\Entity(repositoryClass: TeamSerieRepository::class)]
class TeamSerie
{
    use NbEqualTrait;
    use RankPointChartTrait;
    use PointChartTrait;
    use RankMedalTrait;
    use ChartRank0Trait;
    use ChartRank1Trait;
    use ChartRank2Trait;
    use ChartRank3Trait;
    use PointGameTrait;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name:'team_id', referencedColumnName:'id', nullable:false, onDelete: 'CASCADE')]
    private Team $team;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Serie::class)]
    #[ORM\JoinColumn(name:'serie_id', referencedColumnName:'id', nullable:false, onDelete: 'CASCADE')]
    private Serie $serie;

    public function setSerie(Serie $serie): void
    {
        $this->serie = $serie;
    }

    public function getSerie(): Serie
    {
        return $this->serie;
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
