<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultChartStory;
use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultGameStory;
use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultGroupStory;

class ChartShowTest extends AbstractWebFunctionalTestCase
{
    public function testShowChart(): void
    {
        $game = DefaultGameStory::mario();
        $group = DefaultGroupStory::marioMainGame();
        $chart = DefaultChartStory::marioAnyPercent();

        $url = sprintf(
            '/en/game/%d-%s/group/%d-%s/chart/%d-%s',
            $game->getId(),
            $game->getSlug(),
            $group->getId(),
            $group->getSlug(),
            $chart->getId(),
            $chart->getSlug()
        );
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public function testShowChartNotFound(): void
    {
        $game = DefaultGameStory::mario();
        $group = DefaultGroupStory::marioMainGame();

        $url = sprintf(
            '/en/game/%d-%s/group/%d-%s/chart/99999-nonexistent-chart',
            $game->getId(),
            $game->getSlug(),
            $group->getId(),
            $group->getSlug()
        );
        $this->client->request('GET', $url);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShowChartWithWrongSlug(): void
    {
        $game = DefaultGameStory::mario();
        $group = DefaultGroupStory::marioMainGame();
        $chart = DefaultChartStory::marioAnyPercent();

        $url = sprintf(
            '/en/game/%d-%s/group/%d-%s/chart/%d-wrong-slug',
            $game->getId(),
            $game->getSlug(),
            $group->getId(),
            $group->getSlug(),
            $chart->getId()
        );
        $this->client->request('GET', $url);

        $this->assertResponseStatusCodeSame(404);
    }
}
