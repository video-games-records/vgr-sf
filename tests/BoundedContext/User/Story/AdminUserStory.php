<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Story;

use App\BoundedContext\User\Domain\Entity\User;
use App\Tests\BoundedContext\User\Factory\UserFactory;
use Zenstruck\Foundry\Story;

final class AdminUserStory extends Story
{
    public function build(): void
    {
        // Create the main admin user (same as in fixtures)
        UserFactory::new()
            ->asSuperAdmin()
            ->withCredentials('admin@local.fr', 'admin', 'admin')
            ->create(['id' => 1]);

        // Create additional admin users for testing
        UserFactory::new()
            ->asAdmin()
            ->withCredentials('moderator@local.fr', 'moderator', 'moderator')
            ->create();

        // Create a regular user
        UserFactory::new()
            ->asUser()
            ->withCredentials('user@local.fr', 'user', 'user')
            ->create(['id' => 2]);
    }

    /**
     * Get the main admin user
     */
    public static function adminUser(): User
    {
        return UserFactory::find(['username' => 'admin']);
    }

    /**
     * Get the moderator user
     */
    public static function moderatorUser(): User
    {
        return UserFactory::find(['username' => 'moderator']);
    }

    /**
     * Get the regular user
     */
    public static function regularUser(): User
    {
        return UserFactory::find(['username' => 'user']);
    }
}
