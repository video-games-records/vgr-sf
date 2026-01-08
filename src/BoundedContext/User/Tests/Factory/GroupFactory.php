<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Tests\Factory;

use App\BoundedContext\User\Domain\Entity\Group;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Group>
 */
final class GroupFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Group::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->unique()->words(2, true),
            'roles' => [],
        ];
    }

    /**
     * Create the player group (id=2)
     */
    public static function player(): static
    {
        return static::new()->with([
            'name' => 'Player',
            'roles' => ['ROLE_USER'],
        ]);
    }

    /**
     * Create admin group
     */
    public static function admin(): static
    {
        return static::new()->with([
            'name' => 'Administrator',
            'roles' => ['ROLE_ADMIN'],
        ]);
    }

    /**
     * Create group with specific name and roles
     * @param string[] $roles
     */
    public function withNameAndRoles(string $name, array $roles): static
    {
        return $this->with([
            'name' => $name,
            'roles' => $roles,
        ]);
    }

    /**
     * Create group with specific roles
     * @param string[] $roles
     */
    public function withRoles(array $roles): static
    {
        return $this->with(['roles' => $roles]);
    }
}
