<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerGameRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank0Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank1Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank2Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank3Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank4Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank5Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\Game\GameMethodsTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbChartProvenTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbChartProvenWithoutDlcTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbChartWithoutDlcTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbEqualTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\Player\PlayerMethodsTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankMedalTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointChartTrait;

#[ORM\Table(name:'vgr_player_game')]
#[ORM\Index(name: "idx_last_update", columns: ["player_id", "last_update"])]
#[ORM\Entity(repositoryClass: PlayerGameRepository::class)]
class PlayerGame
{
    use NbChartTrait;
    use NbChartWithoutDlcTrait;
    use NbChartProvenTrait;
    use NbChartProvenWithoutDlcTrait;
    use NbEqualTrait;
    use RankMedalTrait;
    use ChartRank0Trait;
    use ChartRank1Trait;
    use ChartRank2Trait;
    use ChartRank3Trait;
    use ChartRank4Trait;
    use ChartRank5Trait;
    use RankPointChartTrait;
    use PointChartTrait;
    use PlayerMethodsTrait;
    use GameMethodsTrait;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'playerGame')]
    #[ORM\JoinColumn(name:'player_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Player $player;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'playerGame', fetch: 'EAGER')]
    #[ORM\JoinColumn(name:'game_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Game $game;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $pointChartWithoutDlc = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $pointGame = 0;

    #[ORM\Column(nullable: false)]
    private DateTime $lastUpdate;

    /** @var array<string, mixed> */
    private array $statuses;


    public function setPointChartWithoutDlc(int $pointChartWithoutDlc): static
    {
        $this->pointChartWithoutDlc = $pointChartWithoutDlc;
        return $this;
    }

    public function getPointChartWithoutDlc(): int
    {
        return $this->pointChartWithoutDlc;
    }

    public function setPointGame(int $pointGame): static
    {
        $this->pointGame = $pointGame;
        return $this;
    }

    public function getPointGame(): int
    {
        return $this->pointGame;
    }

    public function setLastUpdate(DateTime $lastUpdate): static
    {
        $this->lastUpdate = $lastUpdate;
        return $this;
    }

    public function getLastUpdate(): DateTime
    {
        return $this->lastUpdate;
    }

    /**
     * @param array<string, mixed> $statuses
     */
    public function setStatuses(array $statuses): static
    {
        $this->statuses = $statuses;
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function getId(): string
    {
        return sprintf('player=%d;game=%d', $this->player->getId(), $this->game->getId());
    }
}
