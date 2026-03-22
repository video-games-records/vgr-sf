<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultSerieStory;

class SerieTest extends AbstractWebFunctionalTestCase
{
    public function testSerieIndex(): void
    {
        $this->client->request('GET', '/en/series');

        $this->assertResponseIsSuccessful();
    }

    public function testSerieShow(): void
    {
        $serie = DefaultSerieStory::mario();

        $url = sprintf('/en/serie/%d-%s', $serie->getId(), $serie->getSlug());
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public function testSerieNotFound(): void
    {
        $this->client->request('GET', '/en/serie/99999-nonexistent-serie');

        $this->assertResponseStatusCodeSame(404);
    }
}
