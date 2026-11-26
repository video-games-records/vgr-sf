<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\GameStatus;
use App\Tests\BoundedContext\VideoGamesRecords\Core\Factory\GameFactory;
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

    public function testShowInactiveGameAsAnonymousReturns404(): void
    {
        $game = GameFactory::createOne(['status' => GameStatus::INACTIVE->value]);

        $url = sprintf('/en/game/%d-%s', $game->getId(), $game->getSlug());
        $this->client->request('GET', $url);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShowInactiveGameAsAdminIsAccessibleWithBanner(): void
    {
        $game = GameFactory::createOne(['status' => GameStatus::INACTIVE->value]);

        $this->client->loginUser($this->getAdminUser(), 'user');

        $url = sprintf('/en/game/%d-%s', $game->getId(), $game->getSlug());
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-warning', 'administrator');
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
