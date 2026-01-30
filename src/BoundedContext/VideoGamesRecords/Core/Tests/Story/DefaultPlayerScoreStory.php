<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Tests\Story;

use App\BoundedContext\VideoGamesRecords\Core\Tests\Factory\PlayerChartFactory;
use App\BoundedContext\VideoGamesRecords\Core\Tests\Factory\PlayerChartLibFactory;
use App\BoundedContext\VideoGamesRecords\Core\Tests\Factory\PlayerFactory;
use App\BoundedContext\VideoGamesRecords\Core\Tests\Factory\ChartLibFactory;
use App\BoundedContext\VideoGamesRecords\Core\Tests\Story\DefaultChartStory;
use Zenstruck\Foundry\Story;

final class DefaultPlayerScoreStory extends Story
{
    public function build(): void
    {
        // S'assurer que les charts existent
        DefaultChartStory::load();

        // Récupérer le chart Mario Any% (ID=1) et ses ChartLib
        $marioAnyPercent = DefaultChartStory::marioAnyPercent();
        $marioAnyPercentChartLib = ChartLibFactory::findOrCreate([
            'chart' => $marioAnyPercent,
        ]);

        // Utiliser les players existants créés par UserFixtures (via CreatePlayerListener)

        // Récupérer les players par leur pseudo (plus fiable que par ID)
        $adminPlayer = PlayerFactory::repository()->findOneBy(['pseudo' => 'admin']);
        $userPlayer = PlayerFactory::repository()->findOneBy(['pseudo' => 'user']);
        $moderatorPlayer = PlayerFactory::repository()->findOneBy(['pseudo' => 'moderator']);

        if ($adminPlayer) {
            // Player admin - Meilleur temps (1er place)
            $player1Chart = PlayerChartFactory::new()
                ->forPlayer($adminPlayer)
                ->forChart($marioAnyPercent)
                ->withRank(1)
                ->withPoints(1000)
                ->asTopScore()
                ->create();

            // PlayerChartLib pour Player 1 - 58:32 (3512 secondes)
            PlayerChartLibFactory::new()
                ->forPlayerChart($player1Chart)
                ->withChartLib($marioAnyPercentChartLib)
                ->withTimeValue(0, 58, 32)
                ->create();

            $this->addState('player1_mario_any', $player1Chart);
        }

        if ($userPlayer) {
            // Player user - Deuxième temps (2ème place)
            $player2Chart = PlayerChartFactory::new()
                ->forPlayer($userPlayer)
                ->forChart($marioAnyPercent)
                ->withRank(2)
                ->withPoints(500)
                ->create();

            // PlayerChartLib pour Player 2 - 59:15 (3555 secondes)
            PlayerChartLibFactory::new()
                ->forPlayerChart($player2Chart)
                ->withChartLib($marioAnyPercentChartLib)
                ->withTimeValue(0, 59, 15)
                ->create();

            $this->addState('player2_mario_any', $player2Chart);
        }

        if ($moderatorPlayer) {
            // Player moderator - Troisième temps (3ème place)
            $player3Chart = PlayerChartFactory::new()
                ->forPlayer($moderatorPlayer)
                ->forChart($marioAnyPercent)
                ->withRank(3)
                ->withPoints(250)
                ->create();

            // PlayerChartLib pour Player 3 - 1:01:08 (3668 secondes)
            PlayerChartLibFactory::new()
                ->forPlayerChart($player3Chart)
                ->withChartLib($marioAnyPercentChartLib)
                ->withTimeValue(1, 1, 8)
                ->create();

            $this->addState('player3_mario_any', $player3Chart);
        }
    }

    public static function player1MarioAny(): object
    {
        return static::get('player1_mario_any');
    }

    public static function player2MarioAny(): object
    {
        return static::get('player2_mario_any');
    }

    public static function player3MarioAny(): object
    {
        return static::get('player3_mario_any');
    }
}
