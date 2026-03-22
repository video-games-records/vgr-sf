<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Story\DefaultPlatformStory;

class PlatformTest extends AbstractWebFunctionalTestCase
{
    public function testPlatformIndex(): void
    {
        $this->client->request('GET', '/en/platforms');

        $this->assertResponseIsSuccessful();
    }

    public function testPlatformShow(): void
    {
        $platform = DefaultPlatformStory::nintendoSwitch();

        $url = sprintf('/en/platform/%d-%s', $platform->getId(), $platform->getSlug());
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public function testPlatformNotFound(): void
    {
        $this->client->request('GET', '/en/platform/99999-nonexistent-platform');

        $this->assertResponseStatusCodeSame(404);
    }
}
