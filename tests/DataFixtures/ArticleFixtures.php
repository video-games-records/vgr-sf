<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Tests\BoundedContext\Article\Story\DefaultArticleStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        DefaultArticleStory::load();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
