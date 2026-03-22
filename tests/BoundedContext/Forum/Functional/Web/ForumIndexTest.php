<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Forum\Functional\Web;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class ForumIndexTest extends WebTestCase
{
    use Factories;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testForumIndex(): void
    {
        $this->client->request('GET', '/en/forum');

        $this->assertResponseIsSuccessful();
    }
}
