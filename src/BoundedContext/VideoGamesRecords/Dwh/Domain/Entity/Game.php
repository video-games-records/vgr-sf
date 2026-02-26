<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Dwh\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\DateTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbPostDayTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbPostTrait;

#[ORM\Table(name: 'dwh_game')]
#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    use DateTrait;
    use NbPostDayTrait;
    use NbPostTrait;

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
