<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Functional\Web;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web\AbstractWebFunctionalTestCase;

class ManageTeamBadgesTest extends AbstractWebFunctionalTestCase
{
    public function testManageTeamBadgesRequiresAuthentication(): void
    {
        $this->client->request('GET', '/en/team/badges/manage');

        $this->assertResponseRedirects();
        $this->assertStringContainsString('login', $this->client->getResponse()->headers->get('Location') ?? '');
    }

    public function testReorderTeamBadgesRequiresAuthentication(): void
    {
        $this->client->request('POST', '/en/team/badges/reorder', [], [], ['CONTENT_TYPE' => 'application/json'], '{"order":[1,2,3]}');

        $this->assertResponseRedirects();
        $this->assertStringContainsString('login', $this->client->getResponse()->headers->get('Location') ?? '');
    }

    public function testReorderTeamBadgesWithoutTeamReturnsForbidden(): void
    {
        $user = $this->getPlayerUser();
        $this->client->loginUser($user, 'user');

        $this->client->request('POST', '/en/team/badges/reorder', [], [], ['CONTENT_TYPE' => 'application/json'], '{"order":[1,2,3]}');

        $this->assertResponseStatusCodeSame(403);
    }
}
