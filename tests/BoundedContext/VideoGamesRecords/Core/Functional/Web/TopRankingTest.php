<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web;

class TopRankingTest extends AbstractWebFunctionalTestCase
{
    public function testTopRanking(): void
    {
        $this->client->request('GET', '/en/top-ranking');

        $this->assertResponseIsSuccessful();
    }
}
