<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Dwh\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\DateTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbPostDayTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank0Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank1Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank2Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank3Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank4Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank5Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankMedalTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointGameTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointGameTrait;

#[ORM\Table(name: 'dwh_player')]
#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    use DateTrait;
    use NbPostDayTrait;
    use ChartRank0Trait;
    use ChartRank1Trait;
    use ChartRank2Trait;
    use ChartRank3Trait;
    use ChartRank4Trait;
    use ChartRank5Trait;
    use PointChartTrait;
    use RankPointChartTrait;
    use RankMedalTrait;
    use NbChartTrait;
    use PointGameTrait;
    use RankPointGameTrait;

    #[ORM\Id, ORM\Column]
    private int $id;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank6 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank7 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank8 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank9 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank10 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank11 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank12 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank13 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank14 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank15 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank16 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank17 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank18 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank19 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank20 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank21 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank22 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank23 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank24 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank25 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank26 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank27 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank28 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank29 = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $chartRank30 = 0;

    public function __toString(): string
    {
        return sprintf('%s [%s]', $this->id, $this->id);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setChartRank(int $rank, int $nb): void
    {
        $var = 'chartRank' . $rank;
        $this->$var = $nb;
    }

    public function getChartRank(int $rank): int
    {
        $var = 'chartRank' . $rank;
        return $this->$var;
    }
}
