<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultGameStory;
use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultPlatformStory;
use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultSerieStory;
use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultChartTypeStory;
use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultGroupStory;
use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultChartStory;
use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultPlayerScoreStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class VideoGamesRecordsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Charger d'abord les plateformes puis les séries, puis les jeux, groupes et charts du bounded context VideoGamesRecords
        DefaultPlatformStory::load();
        DefaultSerieStory::load();
        DefaultGameStory::load();
        DefaultGroupStory::load();
        DefaultChartTypeStory::load();
        DefaultChartStory::load();
        DefaultPlayerScoreStory::load();

        // Références utiles pour d'autres fixtures/tests
        $switch = DefaultPlatformStory::nintendoSwitch();
        $pc = DefaultPlatformStory::pc();
        $ps4 = DefaultPlatformStory::ps4();
        $xboxOne = DefaultPlatformStory::xboxOne();

        $mario = DefaultGameStory::mario();
        $zelda = DefaultGameStory::zelda();
        $hollowKnight = DefaultGameStory::hollowKnight();

        $serieMario = DefaultSerieStory::mario();
        $serieZelda = DefaultSerieStory::zelda();
        $serieMetroid = DefaultSerieStory::metroid();

        // Chart types
        $chartTypeScorePlus = DefaultChartTypeStory::scorePlus();
        $chartTypeTime = DefaultChartTypeStory::time();
        $chartTypeScoreMinus = DefaultChartTypeStory::scoreMinus();

        $this->addReference('platform_switch', $switch);
        $this->addReference('platform_pc', $pc);
        $this->addReference('platform_ps4', $ps4);
        $this->addReference('platform_xbox_one', $xboxOne);

        $this->addReference('game_mario_odyssey', $mario);
        $this->addReference('game_zelda_botw', $zelda);
        $this->addReference('game_hollow_knight', $hollowKnight);

        $this->addReference('serie_mario', $serieMario);
        $this->addReference('serie_zelda', $serieZelda);
        $this->addReference('serie_metroid', $serieMetroid);

        // Chart type references
        $this->addReference('chart_type_score_plus', $chartTypeScorePlus);
        $this->addReference('chart_type_time', $chartTypeTime);
        $this->addReference('chart_type_score_minus', $chartTypeScoreMinus);

        // Group references
        $this->addReference('group_mario_main', DefaultGroupStory::marioMainGame());
        $this->addReference('group_mario_moon_rocks', DefaultGroupStory::marioMoonRocks());
        $this->addReference('group_zelda_main_quest', DefaultGroupStory::zeldaMainQuest());
        $this->addReference('group_zelda_side_quests', DefaultGroupStory::zeldaSideQuests());
        $this->addReference('group_zelda_master_trials', DefaultGroupStory::zeldaMasterTrials());
        $this->addReference('group_zelda_champions_ballad', DefaultGroupStory::zeldaChampionsBallad());
        $this->addReference('group_hollow_knight_base', DefaultGroupStory::hollowKnightBase());
        $this->addReference('group_hollow_knight_steel_soul', DefaultGroupStory::hollowKnightSteelSoul());

        // Chart references
        $this->addReference('chart_mario_any_percent', DefaultChartStory::marioAnyPercent());
        $this->addReference('chart_mario_100_percent', DefaultChartStory::mario100Percent());
        $this->addReference('chart_mario_most_moons', DefaultChartStory::marioMostMoons());
        $this->addReference('chart_zelda_any_percent', DefaultChartStory::zeldaAnyPercent());
        $this->addReference('chart_zelda_100_percent', DefaultChartStory::zelda100Percent());
        $this->addReference('chart_zelda_all_shrines', DefaultChartStory::zeldaAllShrines());
        $this->addReference('chart_hollow_knight_any_percent', DefaultChartStory::hollowKnightAnyPercent());
        $this->addReference('chart_hollow_knight_112_percent', DefaultChartStory::hollowKnight112Percent());
        $this->addReference('chart_hollow_knight_steel_soul_any', DefaultChartStory::hollowKnightSteelSoulAny());

        // Player scores references
        $this->addReference('player1_mario_any_score', DefaultPlayerScoreStory::player1MarioAny());
        $this->addReference('player2_mario_any_score', DefaultPlayerScoreStory::player2MarioAny());
        $this->addReference('player3_mario_any_score', DefaultPlayerScoreStory::player3MarioAny());
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            BadgeFixtures::class,
        ];
    }
}
