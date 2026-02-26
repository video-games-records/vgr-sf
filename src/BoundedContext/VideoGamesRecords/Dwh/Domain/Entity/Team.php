<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Dwh\Infrastructure\Doctrine\Repository\TeamRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\DateTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbPostDayTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank0Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank1Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank2Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank3Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankMedalTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointBadgeTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointBadgeTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbMasterBadgeTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointGameTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointGameTrait;

#[ORM\Table(name: 'dwh_team')]
#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    use DateTrait;
    use NbPostDayTrait;
    use ChartRank0Trait;
    use ChartRank1Trait;
    use ChartRank2Trait;
    use ChartRank3Trait;
    use PointChartTrait;
    use RankPointChartTrait;
    use RankMedalTrait;
    use RankPointBadgeTrait;
    use PointBadgeTrait;
    use NbMasterBadgeTrait;
    use PointGameTrait;
    use RankPointGameTrait;

    #[ORM\Id, ORM\Column]
    private int $id;

    public function __toString(): string
    {
        return sprintf('%s [%s]', $this->id, $this->id);
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /** @param array<string, mixed> $row */
    public function setFromArray(array $row): void
    {
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
    }
}
