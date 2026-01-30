<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Tests\Story;

use App\BoundedContext\VideoGamesRecords\Core\Tests\Factory\ChartTypeFactory;
use Zenstruck\Foundry\Story;

final class DefaultChartTypeStory extends Story
{
    public function build(): void
    {
        // 1. Score (+) -> mask 30~, tri DESC
        $this->addState('scorePlus', ChartTypeFactory::findOrCreate([
            'name' => 'Score (+)',
        ], [
            'name' => 'Score (+)',
            'mask' => '30~',
            'orderBy' => 'DESC',
        ]));

        // 2. Temps (XXX:XX.XX) (-) -> mask 30~:|2~.|2~, tri ASC
        $this->addState('time', ChartTypeFactory::findOrCreate([
            'name' => 'Temps (XXX:XX.XX) (-)',
        ], [
            'name' => 'Temps (XXX:XX.XX) (-)',
            'mask' => '30~:|2~.|2~',
            'orderBy' => 'ASC',
        ]));

        // 3. Score (-) -> mask 30~, tri ASC
        $this->addState('scoreMinus', ChartTypeFactory::findOrCreate([
            'name' => 'Score (-)',
        ], [
            'name' => 'Score (-)',
            'mask' => '30~',
            'orderBy' => 'ASC',
        ]));
    }

    public static function scorePlus(): object
    {
        return static::get('scorePlus');
    }

    public static function time(): object
    {
        return static::get('time');
    }

    public static function scoreMinus(): object
    {
        return static::get('scoreMinus');
    }
}
