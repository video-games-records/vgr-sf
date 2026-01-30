<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Tests\Factory;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<PlayerChart>
 */
final class PlayerChartFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return PlayerChart::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    protected function defaults(): array|callable
    {
        return [
            'player' => PlayerFactory::new(),
            'chart' => ChartFactory::new(),
            'platform' => null,
            'rank' => 0,
            'pointChart' => 0,
            'isTopScore' => false,
            'status' => PlayerChartStatusEnum::NONE,
        ];
    }

    /**
     * Set the player relation
     */
    public function forPlayer(Player $player): static
    {
        return $this->with(['player' => $player]);
    }

    /**
     * Set the player by ID (for existing players)
     */
    public function forPlayerId(int $playerId): static
    {
        // Trouver le player qui doit exister (créé par UserFixtures via CreatePlayerListener)
        $player = PlayerFactory::repository()->find($playerId);
        if (!$player) {
            throw new \Exception("Player with ID {$playerId} not found. Make sure UserFixtures created this player via CreatePlayerListener.");
        }
        return $this->with(['player' => $player]);
    }

    /**
     * Set the chart relation
     */
    public function forChart(Chart $chart): static
    {
        return $this->with(['chart' => $chart]);
    }

    /**
     * Set the platform relation
     */
    public function withPlatform(?Platform $platform): static
    {
        return $this->with(['platform' => $platform]);
    }

    /**
     * Set the rank
     */
    public function withRank(int $rank): static
    {
        return $this->with(['rank' => $rank]);
    }

    /**
     * Set the points
     */
    public function withPoints(int $points): static
    {
        return $this->with(['pointChart' => $points]);
    }

    /**
     * Mark as top score
     */
    public function asTopScore(): static
    {
        return $this->with(['isTopScore' => true]);
    }

    /**
     * Set the status enum
     */
    public function withStatus(PlayerChartStatusEnum $status): static
    {
        return $this->with(['status' => $status]);
    }
}
