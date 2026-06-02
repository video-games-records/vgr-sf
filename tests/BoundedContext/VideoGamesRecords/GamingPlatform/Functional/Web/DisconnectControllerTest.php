<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\GamingPlatform\Functional\Web;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web\AbstractWebFunctionalTestCase;

class DisconnectControllerTest extends AbstractWebFunctionalTestCase
{
    public function testDisconnectRequiresAuthentication(): void
    {
        $this->client->request('POST', '/en/platform/steam/disconnect');

        $this->assertResponseRedirects();
        $this->assertStringContainsString('login', $this->client->getResponse()->headers->get('Location') ?? '');
    }

    public function testDisconnectWithInvalidCsrfThrowsAccessDenied(): void
    {
        $user = $this->getPlayerUser();
        $this->client->loginUser($user, 'user');

        $this->client->request('POST', '/en/platform/steam/disconnect', [
            '_token' => 'invalid-token',
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDisconnectRequiresPostMethod(): void
    {
        $user = $this->getPlayerUser();
        $this->client->loginUser($user, 'user');

        $this->client->request('GET', '/en/platform/steam/disconnect');

        $this->assertResponseStatusCodeSame(405);
    }
}
