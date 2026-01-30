<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Tests\Story;

use App\BoundedContext\VideoGamesRecords\Core\Tests\Factory\SerieFactory;
use Zenstruck\Foundry\Story;

final class DefaultSerieStory extends Story
{
    public function build(): void
    {
        // Séries de référence
        SerieFactory::new()
            ->withName('Super Mario')
            ->active()
            ->create(['id' => 1]);

        SerieFactory::new()
            ->withName('The Legend of Zelda')
            ->active()
            ->create(['id' => 2]);

        SerieFactory::new()
            ->withName('Metroid')
            ->active()
            ->create(['id' => 3]);

        // Séries supplémentaires aléatoires
        SerieFactory::new()
            ->inactive()
            ->many(2)
            ->create();
    }

    public static function mario(): object
    {
        return SerieFactory::find(['libSerie' => 'Super Mario']);
    }

    public static function zelda(): object
    {
        return SerieFactory::find(['libSerie' => 'The Legend of Zelda']);
    }

    public static function metroid(): object
    {
        return SerieFactory::find(['libSerie' => 'Metroid']);
    }
}
