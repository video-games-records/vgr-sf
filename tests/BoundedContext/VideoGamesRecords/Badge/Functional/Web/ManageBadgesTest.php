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
}
