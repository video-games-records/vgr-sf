<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Tests\Factory;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\SerieStatus;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Serie>
 */
final class SerieFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Serie::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    protected function defaults(): array|callable
    {
        return [
            'libSerie' => self::faker()->unique()->words(2, true),
            'status' => SerieStatus::INACTIVE,
        ];
    }

    /**
     * Mark serie as active
     */
    public function active(): static
    {
        return $this->with(['status' => SerieStatus::ACTIVE]);
    }

    /**
     * Mark serie as inactive
     */
    public function inactive(): static
    {
        return $this->with(['status' => SerieStatus::INACTIVE]);
    }

    /**
     * Override serie name
     */
    public function withName(string $name): static
    {
        return $this->with(['libSerie' => $name]);
    }
}
