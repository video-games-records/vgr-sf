<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Tests\Story;

use App\BoundedContext\VideoGamesRecords\Core\Tests\Factory\GroupFactory;
use App\BoundedContext\VideoGamesRecords\Core\Tests\Story\DefaultGameStory;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\GroupOrderBy;
use Zenstruck\Foundry\Story;

final class DefaultGroupStory extends Story
{
    public function build(): void
    {
        // S'assurer que les jeux par défaut existent
        DefaultGameStory::load();

        $mario = DefaultGameStory::mario();
        $zelda = DefaultGameStory::zelda();
        $hollowKnight = DefaultGameStory::hollowKnight();

        // Groupes pour Super Mario Odyssey
        GroupFactory::new()
            ->withNames('Main Game', 'Jeu Principal')
            ->withOrderBy(GroupOrderBy::NAME)
            ->forGame($mario)
            ->ranked()
            ->create([
                'id' => 1,
            ]);

        GroupFactory::new()
            ->withNames('Moon Rocks', 'Roches Lunaires')
            ->withOrderBy(GroupOrderBy::NAME)
            ->forGame($mario)
            ->dlc()
            ->create([
                'id' => 2,
            ]);

        // Groupes pour The Legend of Zelda: Breath of the Wild
        GroupFactory::new()
            ->withNames('Main Quest', 'Quête Principale')
            ->withOrderBy(GroupOrderBy::NAME)
            ->forGame($zelda)
            ->ranked()
            ->create([
                'id' => 3,
            ]);

        GroupFactory::new()
            ->withNames('Side Quests', 'Quêtes Secondaires')
            ->withOrderBy(GroupOrderBy::NAME)
            ->forGame($zelda)
            ->create([
                'id' => 4,
            ]);

        GroupFactory::new()
            ->withNames('DLC - The Master Trials', 'DLC - Les Épreuves Légendaires')
            ->withOrderBy(GroupOrderBy::NAME)
            ->forGame($zelda)
            ->dlc()
            ->create([
                'id' => 5,
            ]);

        GroupFactory::new()
            ->withNames('DLC - The Champions\' Ballad', 'DLC - L\'Ode aux Prodiges')
            ->withOrderBy(GroupOrderBy::NAME)
            ->forGame($zelda)
            ->dlc()
            ->create([
                'id' => 6,
            ]);

        // Groupes pour Hollow Knight
        GroupFactory::new()
            ->withNames('Base Game', 'Jeu de Base')
            ->withOrderBy(GroupOrderBy::NAME)
            ->forGame($hollowKnight)
            ->ranked()
            ->create([
                'id' => 7,
            ]);

        GroupFactory::new()
            ->withNames('Steel Soul Mode', 'Mode Âme d\'Acier')
            ->withOrderBy(GroupOrderBy::NAME)
            ->forGame($hollowKnight)
            ->create([
                'id' => 8,
            ]);

        // Quelques groupes supplémentaires aléatoires
        GroupFactory::new()
            ->many(3)
            ->create();
    }

    public static function marioMainGame(): object
    {
        return GroupFactory::find(['libGroupEn' => 'Main Game']);
    }

    public static function marioMoonRocks(): object
    {
        return GroupFactory::find(['libGroupEn' => 'Moon Rocks']);
    }

    public static function zeldaMainQuest(): object
    {
        return GroupFactory::find(['libGroupEn' => 'Main Quest']);
    }

    public static function zeldaSideQuests(): object
    {
        return GroupFactory::find(['libGroupEn' => 'Side Quests']);
    }

    public static function zeldaMasterTrials(): object
    {
        return GroupFactory::find(['libGroupEn' => 'DLC - The Master Trials']);
    }

    public static function zeldaChampionsBallad(): object
    {
        return GroupFactory::find(['libGroupEn' => 'DLC - The Champions\' Ballad']);
    }

    public static function hollowKnightBase(): object
    {
        return GroupFactory::find(['libGroupEn' => 'Base Game']);
    }

    public static function hollowKnightSteelSoul(): object
    {
        return GroupFactory::find(['libGroupEn' => 'Steel Soul Mode']);
    }
}
