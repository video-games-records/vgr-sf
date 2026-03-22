<?php

declare(strict_types=1);

namespace App\Tests\SharedKernel\Functional\Web;

class HealthCheckTest extends AbstractWebTestCase
{
    public function testHealth(): void
    {
        $this->client->request('GET', '/health');

        $this->assertResponseIsSuccessful();
    }

    public function testLive(): void
    {
        $this->client->request('GET', '/health/live');

        $this->assertResponseIsSuccessful();
    }

    public function testReady(): void
    {
        $this->client->request('GET', '/health/ready');

        $this->assertResponseIsSuccessful();
    }

    public function testDetailedRequiresAuthentication(): void
    {
        $this->client->request('GET', '/health/detailed');

        $this->assertResponseRedirects();
    }
}
