<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Story;

use App\Tests\BoundedContext\VideoGamesRecords\Badge\Factory\BadgeFactory;
use Zenstruck\Foundry\Story;

/**
 * @method static object register()
 * @method static object connection()
 * @method static object forum()
 */
final class BadgeStory extends Story
{
    public function build(): void
    {
        // Badge d'inscription avec ID=1 (requis par CreatePlayerListener)
        $this->addState('register', BadgeFactory::findOrCreate([
            'id' => 1,
            'type' => \App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType::INSCRIPTION,
            'picture' => 'inscription.gif',
            'value' => 0,
        ]));

        // Autres badges pour les tests
        $this->addState('connection', BadgeFactory::new()->connection()->create());
        $this->addState('forum', BadgeFactory::new()->forum()->create());
    }
}
