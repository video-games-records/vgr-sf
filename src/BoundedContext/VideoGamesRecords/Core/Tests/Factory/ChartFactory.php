<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Tests\Factory;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Chart>
 */
final class ChartFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Chart::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    protected function defaults(): array|callable
    {
        return [
            'libChartEn' => self::faker()->unique()->words(2, true),
            'libChartFr' => self::faker()->unique()->words(2, true),
            'isProofVideoOnly' => false,
            'group' => GroupFactory::new(),
        ];
    }

    /**
     * Override names
     */
    public function withNames(string $en, ?string $fr = null): static
    {
        return $this->with([
            'libChartEn' => $en,
            'libChartFr' => $fr ?? $en,
        ]);
    }

    /**
     * Set the group relation
     */
    public function forGroup(object $group): static
    {
        return $this->with(['group' => $group]);
    }

    /**
     * Mark as DLC chart
     */
    public function dlc(): static
    {
        return $this->with(['isDlc' => true]);
    }

    /**
     * Require video proof only
     */
    public function videoProofOnly(): static
    {
        return $this->with(['isProofVideoOnly' => true]);
    }

    /**
     * Allow picture proof
     */
    public function pictureProofAllowed(): static
    {
        return $this->with(['isProofVideoOnly' => false]);
    }

    /**
     * Create a speedrun chart
     */
    public function speedrun(): static
    {
        return $this->withNames('Any% Speedrun', 'Speedrun Any%')
            ->videoProofOnly();
    }

    /**
     * Create a high score chart
     */
    public function highScore(): static
    {
        return $this->withNames('High Score', 'Meilleur Score')
            ->pictureProofAllowed();
    }

    /**
     * Create a completion time chart
     */
    public function completionTime(): static
    {
        return $this->withNames('Completion Time', 'Temps de Completion')
            ->videoProofOnly();
    }
}
