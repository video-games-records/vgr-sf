<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web;

class PlayerIndexTest extends AbstractWebFunctionalTestCase
{
    public function testPlayerIndex(): void
    {
        $this->client->request('GET', '/en/players');

        $this->assertResponseIsSuccessful();
    }
}
