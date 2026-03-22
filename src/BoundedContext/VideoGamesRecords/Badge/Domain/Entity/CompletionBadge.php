<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;

#[ORM\Entity]
class CompletionBadge extends Badge
{
    #[ORM\OneToOne(targetEntity: Game::class, mappedBy: 'completionBadge')]
    private ?Game $game = null;

    public function __construct()
    {
        $this->setType(BadgeType::COMPLETION);
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

        $this->setValue($game->getNbChartWithoutDlc());
    }
}
