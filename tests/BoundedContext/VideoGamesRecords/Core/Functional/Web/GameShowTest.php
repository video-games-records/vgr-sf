<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultGameStory;

class GameShowTest extends AbstractWebFunctionalTestCase
{
    public function testShowGame(): void
    {
        $game = DefaultGameStory::mario();

        $url = sprintf('/en/game/%d-%s', $game->getId(), $game->getSlug());
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public function testShowGameNotFound(): void
    {
        $this->client->request('GET', '/en/game/99999-nonexistent-game');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShowGameWithWrongSlug(): void
    {
        $game = DefaultGameStory::mario();

        $url = sprintf('/en/game/%d-wrong-slug', $game->getId());
        $this->client->request('GET', $url);

        $this->assertResponseStatusCodeSame(404);
    }
}
