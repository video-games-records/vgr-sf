<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultGameStory;
use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultGroupStory;

class GroupShowTest extends AbstractWebFunctionalTestCase
{
    public function testShowGroup(): void
    {
        $game = DefaultGameStory::mario();
        $group = DefaultGroupStory::marioMainGame();

        $url = sprintf(
            '/en/game/%d-%s/group/%d-%s',
            $game->getId(),
            $game->getSlug(),
            $group->getId(),
            $group->getSlug()
        );
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public function testShowGroupNotFound(): void
    {
        $game = DefaultGameStory::mario();

        $url = sprintf('/en/game/%d-%s/group/99999-nonexistent-group', $game->getId(), $game->getSlug());
        $this->client->request('GET', $url);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShowGroupWithWrongSlug(): void
    {
        $game = DefaultGameStory::mario();
        $group = DefaultGroupStory::marioMainGame();

        $url = sprintf(
            '/en/game/%d-%s/group/%d-wrong-slug',
            $game->getId(),
            $game->getSlug(),
            $group->getId()
        );
        $this->client->request('GET', $url);

        $this->assertResponseStatusCodeSame(404);
    }
}
