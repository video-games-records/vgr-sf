<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Team\Functional\Web;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class TeamIndexTest extends WebTestCase
{
    use Factories;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testTeamIndex(): void
    {
        $this->client->request('GET', '/en/teams');

        $this->assertResponseIsSuccessful();
    }
}
