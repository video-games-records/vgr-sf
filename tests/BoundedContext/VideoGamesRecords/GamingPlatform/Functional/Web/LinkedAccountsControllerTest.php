<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\GamingPlatform\Functional\Web;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web\AbstractWebFunctionalTestCase;

class LinkedAccountsControllerTest extends AbstractWebFunctionalTestCase
{
    public function testLinkedAccountsRequiresAuthentication(): void
    {
        $this->client->request('GET', '/profile/platforms');

        $this->assertResponseRedirects();
        $this->assertStringContainsString('login', $this->client->getResponse()->headers->get('Location') ?? '');
    }

    public function testLinkedAccountsIsAccessibleWhenLoggedIn(): void
    {
        $user = $this->getPlayerUser();
        $this->client->loginUser($user, 'user');

        $this->client->request('GET', '/profile/platforms');

        $this->assertResponseIsSuccessful();
    }
}
