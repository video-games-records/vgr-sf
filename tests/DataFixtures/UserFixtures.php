<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Tests\BoundedContext\User\Story\GroupStory;
use App\Tests\BoundedContext\User\Story\AdminUserStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Charger les groupes d'utilisateurs
        GroupStory::load();

        // Use the AdminUserStory to create consistent users for both fixtures and tests
        AdminUserStory::load();

        // Create references for other fixtures that might need them
        $adminUser = AdminUserStory::adminUser();
        $regularUser = AdminUserStory::regularUser();

        $this->addReference('user1', $adminUser);
        $this->addReference('user2', $regularUser);
        $this->addReference('group_player', GroupStory::player());
        $this->addReference('group_admin', GroupStory::admin());
    }

    public function getDependencies(): array
    {
        return [
            BadgeFixtures::class,
        ];
    }
}
