<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Tests\Factory;

use App\BoundedContext\User\Domain\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->unique()->safeEmail(),
            'username' => self::faker()->unique()->userName(),
            'roles' => [],
            'enabled' => true,
            'plainPassword' => 'password',
        ];
    }

    protected function initialize(): static
    {
        return $this->beforeInstantiate(function (array $attributes): array {
            // Remove plainPassword from attributes as it's not a Doctrine field
            // but store it for use in afterInstantiate
            return $attributes;
        })->afterInstantiate(function (User $user, array $attributes): void {
            if (isset($attributes['plainPassword'])) {
                $user->setPlainPassword($attributes['plainPassword']);
            }
        });
    }

    /**
     * Create a regular user
     */
    public function asUser(): static
    {
        return $this->with([
            'roles' => [],
        ]);
    }

    /**
     * Create an admin user
     */
    public function asAdmin(): static
    {
        return $this->with([
            'roles' => ['ROLE_ADMIN'],
        ]);
    }

    /**
     * Create a super admin user
     */
    public function asSuperAdmin(): static
    {
        return $this->with([
            'roles' => ['ROLE_SUPER_ADMIN'],
        ]);
    }

    /**
     * Create a user with specific email and username
     */
    public function withCredentials(string $email, string $username, string $password = 'password'): static
    {
        return $this->with([
            'email' => $email,
            'username' => $username,
            'plainPassword' => $password,
        ]);
    }

    /**
     * Create a disabled user
     */
    public function disabled(): static
    {
        return $this->with([
            'enabled' => false,
        ]);
    }

    /**
     * Create a user with custom roles
     * @param string[] $roles
     */
    public function withRoles(array $roles): static
    {
        return $this->with([
            'roles' => $roles,
        ]);
    }
}
