<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Tests\Factory;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartLib;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<PlayerChartLib>
 */
final class PlayerChartLibFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return PlayerChartLib::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    protected function defaults(): array|callable
    {
        return [
            'value' => '0',
            'playerChart' => PlayerChartFactory::new(),
            'libChart' => ChartLibFactory::new(),
        ];
    }

    /**
     * Set the value (as string for bigint support)
     */
    public function withValue(string $value): static
    {
        return $this->with(['value' => $value]);
    }

    /**
     * Set the player chart relation
     */
    public function forPlayerChart(PlayerChart $playerChart): static
    {
        return $this->with(['playerChart' => $playerChart]);
    }

    /**
     * Set the chart lib relation
     */
    public function withChartLib(ChartLib $chartLib): static
    {
        return $this->with(['libChart' => $chartLib]);
    }

    /**
     * Create with time value (for time charts)
     * Format: seconds as string (e.g., "3723" for 01:02:03)
     */
    public function withTimeValue(int $hours, int $minutes, int $seconds): static
    {
        $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
        return $this->with(['value' => (string) $totalSeconds]);
    }

    /**
     * Create with score value (for score charts)
     */
    public function withScoreValue(int $score): static
    {
        return $this->with(['value' => (string) $score]);
    }
}
