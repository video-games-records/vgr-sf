<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultPlayerStory;

class PlayerProfileTest extends AbstractWebFunctionalTestCase
{
    public function testPlayerProfileOverview(): void
    {
        DefaultPlayerStory::load();
        $player = DefaultPlayerStory::topPlayer();

        $url = sprintf('/en/player/%d-%s', $player->getId(), $player->getSlug());
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public function testPlayerProfileGames(): void
    {
        DefaultPlayerStory::load();
        $player = DefaultPlayerStory::topPlayer();

        $url = sprintf('/en/player/%d-%s/games', $player->getId(), $player->getSlug());
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public function testPlayerProfileBadges(): void
    {
        DefaultPlayerStory::load();
        $player = DefaultPlayerStory::topPlayer();

        $url = sprintf('/en/player/%d-%s/badges', $player->getId(), $player->getSlug());
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public function testPlayerProfileNotFound(): void
    {
        $this->client->request('GET', '/en/player/99999-nonexistent-player');

        $this->assertResponseStatusCodeSame(404);
    }
}
