<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Factory;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Platform>
 */
final class PlatformFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Platform::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->unique()->words(2, true),
            'picture' => 'bt_default.png',
            'status' => 'INACTIF',
        ];
    }

    /**
     * Mark platform as active
     */
    public function active(): static
    {
        return $this->with(['status' => 'ACTIF']);
    }

    /**
     * Mark platform as inactive
     */
    public function inactive(): static
    {
        return $this->with(['status' => 'INACTIF']);
    }

    /**
     * Override platform name
     */
    public function withName(string $name): static
    {
        return $this->with(['name' => $name]);
    }
}
