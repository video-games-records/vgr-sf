<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Tests\Story;

use App\BoundedContext\User\Tests\Factory\GroupFactory;
use Zenstruck\Foundry\Story;

final class GroupStory extends Story
{
    public function build(): void
    {
        // Groupe Player avec ID=2 (requis par CreatePlayerListener)
        $this->addState('player', GroupFactory::player()->create(['id' => 2]));

        // Autres groupes pour les tests
        $this->addState('admin', GroupFactory::admin()->create());
    }
}
