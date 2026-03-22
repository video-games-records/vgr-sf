<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web;

class LatestScoresTest extends AbstractWebFunctionalTestCase
{
    public function testLatestScores(): void
    {
        $this->client->request('GET', '/en/scores/latest');

        $this->assertResponseIsSuccessful();
    }
}
