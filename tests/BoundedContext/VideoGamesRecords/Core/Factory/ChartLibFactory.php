<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Factory;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartLib;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ChartLib>
 */
final class ChartLibFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return ChartLib::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    protected function defaults(): array|callable
    {
        return [
            'name' => null,
            'chart' => ChartFactory::new(),
            'type' => ChartTypeFactory::new(),
        ];
    }

    /**
     * Set the chart relation
     */
    public function forChart(object $chart): static
    {
        return $this->with(['chart' => $chart]);
    }

    /**
     * Set the chart type relation
     */
    public function withType(object $type): static
    {
        return $this->with(['type' => $type]);
    }

    /**
     * Set the name
     */
    public function withName(?string $name): static
    {
        return $this->with(['name' => $name]);
    }
}
