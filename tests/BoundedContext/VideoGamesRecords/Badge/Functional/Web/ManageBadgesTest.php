<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Functional\Web;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web\AbstractWebFunctionalTestCase;

class ManageBadgesTest extends AbstractWebFunctionalTestCase
{
    public function testManageBadgesRequiresAuthentication(): void
    {
        $this->client->request('GET', '/en/player/badges/manage');

        $this->assertResponseRedirects();
        $this->assertStringContainsString('login', $this->client->getResponse()->headers->get('Location') ?? '');
    }

    public function testReorderBadgesRequiresAuthentication(): void
    {
        $this->client->request('POST', '/en/player/badges/reorder', [], [], ['CONTENT_TYPE' => 'application/json'], '{"order":[1,2,3]}');

        $this->assertResponseRedirects();
        $this->assertStringContainsString('login', $this->client->getResponse()->headers->get('Location') ?? '');
    }

    public function testReorderBadgesWithInvalidPayloadReturnsBadRequest(): void
    {
        $user = $this->getPlayerUser();
        $this->client->loginUser($user, 'user');

        $this->client->request('POST', '/en/player/badges/reorder', [], [], ['CONTENT_TYPE' => 'application/json'], '{"invalid":"data"}');

        $this->assertResponseStatusCodeSame(400);
    }
}
