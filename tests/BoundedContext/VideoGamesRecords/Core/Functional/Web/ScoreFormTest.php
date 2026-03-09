<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultGameStory;

class ScoreFormTest extends AbstractWebFunctionalTestCase
{
    public function testScoreFormRequiresAuthentication(): void
    {
        $game = DefaultGameStory::mario();

        $url = sprintf('/en/game/%d-%s/scores', $game->getId(), $game->getSlug());
        $this->client->request('GET', $url);

        // Unauthenticated users are redirected to login
        $this->assertResponseRedirects();
        $this->assertStringContainsString('login', $this->client->getResponse()->headers->get('Location') ?? '');
    }

    public function testScoreFormDisplaysForAuthenticatedPlayer(): void
    {
        $user = $this->getPlayerUser();
        $game = DefaultGameStory::mario();

        $this->client->loginUser($user, 'user');

        $url = sprintf('/en/game/%d-%s/scores', $game->getId(), $game->getSlug());
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public function testEmptyScoreSubmissionRedirectsWithWarning(): void
    {
        $user = $this->getPlayerUser();
        $game = DefaultGameStory::mario();

        $this->client->loginUser($user, 'user');

        $url = sprintf('/en/game/%d-%s/scores', $game->getId(), $game->getSlug());
        $crawler = $this->client->request('GET', $url);

        $form = $crawler->filter('form#score-form')->form();
        $this->client->submit($form, []);

        // Empty submission redirects back (URL may include query params like ?page=1)
        $this->assertResponseRedirects();
        $this->assertStringContainsString(
            sprintf('/en/game/%d-%s/scores', $game->getId(), $game->getSlug()),
            $this->client->getResponse()->headers->get('Location') ?? ''
        );
    }
}
