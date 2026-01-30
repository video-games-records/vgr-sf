<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Tests\Story;

use App\BoundedContext\VideoGamesRecords\Badge\Tests\Factory\BadgeFactory;
use Zenstruck\Foundry\Story;

final class BadgeStory extends Story
{
    public function build(): void
    {
        // Badge d'inscription avec ID=1 (requis par CreatePlayerListener)
        $this->addState('register', BadgeFactory::findOrCreate(['id' => 1], [
            'type' => \App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType::INSCRIPTION,
            'picture' => 'inscription.gif',
            'value' => 0,
        ]));

        // Autres badges pour les tests
        $this->addState('connection', BadgeFactory::new()->connection()->create());
        $this->addState('forum', BadgeFactory::new()->forum()->create());
    }
}
