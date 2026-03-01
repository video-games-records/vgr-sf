<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;

#[ORM\Entity]
class MasterBadge extends Badge
{
    #[ORM\OneToOne(targetEntity: Game::class, mappedBy: 'badge')]
    private ?Game $game = null;

    public function __construct()
    {
        $this->setType(BadgeType::MASTER);
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function majValue(?Game $game = null): void
    {
        $game = $game ?? $this->game;

        if ($game === null) {
            $this->setValue(0);
            return;
        }

        if (0 === $this->getNbPlayer()) {
            $this->setValue(0);
        } else {
            $nbPlayerDiff = 100 + $game->getNbPlayer() - $this->getNbPlayer();
            $factor = 6250 * (-1 / $nbPlayerDiff + 0.0102);
            $divisor = pow($this->getNbPlayer(), 1 / 3);
            $this->setValue((int) floor(100 * $factor / $divisor));
        }
    }
}
