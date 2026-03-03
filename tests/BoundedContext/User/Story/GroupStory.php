<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Story;

use App\Tests\BoundedContext\User\Factory\GroupFactory;
use Zenstruck\Foundry\Story;

/**
 * @method static object player()
 * @method static object admin()
 */
final class GroupStory extends Story
{
    public function build(): void
    {
        // id=1 : groupe quelconque (pour que le Player obtienne id=2)
        // CreatePlayerListener::GROUP_PLAYER = 2 impose que le groupe Player soit à id=2
        $this->addState('admin', GroupFactory::admin()->create());

        // id=2 : groupe Player (requis par CreatePlayerListener::GROUP_PLAYER = 2)
        $this->addState('player', GroupFactory::player()->create());
    }
}
