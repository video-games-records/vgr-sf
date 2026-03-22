<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Message\Functional\Web;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web\AbstractWebFunctionalTestCase;

class MessageTest extends AbstractWebFunctionalTestCase
{
    public function testInboxRequiresAuthentication(): void
    {
        $this->client->request('GET', '/en/messages/inbox');

        $this->assertResponseRedirects();
        $this->assertStringContainsString('login', $this->client->getResponse()->headers->get('Location') ?? '');
    }

    public function testInboxForAuthenticatedUser(): void
    {
        $user = $this->getPlayerUser();
        $this->client->loginUser($user, 'user');

        $this->client->request('GET', '/en/messages/inbox');

        $this->assertResponseIsSuccessful();
    }

    public function testOutboxForAuthenticatedUser(): void
    {
        $user = $this->getPlayerUser();
        $this->client->loginUser($user, 'user');

        $this->client->request('GET', '/en/messages/outbox');

        $this->assertResponseIsSuccessful();
    }

    public function testComposeForAuthenticatedUser(): void
    {
        $user = $this->getPlayerUser();
        $this->client->loginUser($user, 'user');

        $this->client->request('GET', '/en/messages/compose');

        $this->assertResponseIsSuccessful();
    }
}
