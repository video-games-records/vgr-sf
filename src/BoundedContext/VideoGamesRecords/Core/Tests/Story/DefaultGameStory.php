<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Tests\Story;

use App\BoundedContext\VideoGamesRecords\Core\Tests\Factory\GameFactory;
use App\BoundedContext\VideoGamesRecords\Core\Tests\Story\DefaultPlatformStory;
use App\BoundedContext\VideoGamesRecords\Core\Tests\Story\DefaultSerieStory;
use Zenstruck\Foundry\Story;

final class DefaultGameStory extends Story
{
    public function build(): void
    {
        // S'assurer que les plateformes par défaut existent
        DefaultPlatformStory::load();
        // S'assurer que les séries par défaut existent
        DefaultSerieStory::load();

        $switch = DefaultPlatformStory::nintendoSwitch();
        $pc = DefaultPlatformStory::pc();
        $ps4 = DefaultPlatformStory::ps4();
        $xboxOne = DefaultPlatformStory::xboxOne();

        $marioSerie = DefaultSerieStory::mario();
        $zeldaSerie = DefaultSerieStory::zelda();

        // Quelques jeux de référence utilisés dans les tests
        GameFactory::new()
            ->withNames('Super Mario Odyssey', 'Super Mario Odyssey')
            ->published()
            ->create([
                'id' => 1,
                'platforms' => [$switch],
                // Mario Odyssey appartient à la série Super Mario
                'serie' => $marioSerie,
            ]);

        GameFactory::new()
            ->withNames('The Legend of Zelda: Breath of the Wild', 'The Legend of Zelda: Breath of the Wild')
            ->published()
            ->create([
                'id' => 2,
                'platforms' => [$switch],
                // BOTW appartient à la série The Legend of Zelda
                'serie' => $zeldaSerie,
            ]);

        GameFactory::new()
            ->withNames('Hollow Knight', 'Hollow Knight')
            ->draft()
            ->create([
                'id' => 3,
                // Hollow Knight est multi-plateformes
                'platforms' => [$pc, $switch, $ps4, $xboxOne],
                // Pas de série obligatoire: on laisse null pour l'exemple
            ]);

        // Quelques jeux supplémentaires aléatoires
        GameFactory::new()
            ->many(3)
            ->create();
    }

    public static function mario(): object
    {
        return GameFactory::find(['libGameEn' => 'Super Mario Odyssey']);
    }

    public static function zelda(): object
    {
        return GameFactory::find(['libGameEn' => 'The Legend of Zelda: Breath of the Wild']);
    }

    public static function hollowKnight(): object
    {
        return GameFactory::find(['libGameEn' => 'Hollow Knight']);
    }
}
