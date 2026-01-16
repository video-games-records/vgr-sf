<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Domain\Entity;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamGameRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank0Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank1Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank2Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank3Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbEqualTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointGameTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankMedalTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointChartTrait;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;

#[ORM\Table(name:'vgr_team_game')]
#[ORM\Entity(repositoryClass: TeamGameRepository::class)]
class TeamGame
{
    use NbEqualTrait;
    use RankPointChartTrait;
    use PointChartTrait;
    //use RankPointGameTrait;
    use PointGameTrait;
    use RankMedalTrait;
    use ChartRank0Trait;
    use ChartRank1Trait;
    use ChartRank2Trait;
    use ChartRank3Trait;

    #[ApiProperty(identifier: false)]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'teamGame')]
    #[ORM\JoinColumn(name:'team_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Team $team;

    #[ApiProperty(identifier: false)]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'teamGame', fetch: 'EAGER')]
    #[ORM\JoinColumn(name:'game_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Game $game;

    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    public function getGame(): Game
    {
        return $this->game;
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
        return sprintf('team=%d;game=%d', $this->team->getId(), $this->game->getId());
    }
}
