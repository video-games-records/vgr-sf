<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\BoundedContext\VideoGamesRecords\Badge\Tests\Story\BadgeStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BadgeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Charger les badges essentiels
        BadgeStory::load();

        // Référence pour le badge d'inscription (utilisé par CreatePlayerListener)
        $this->addReference('badge_register', BadgeStory::register());
    }
}
