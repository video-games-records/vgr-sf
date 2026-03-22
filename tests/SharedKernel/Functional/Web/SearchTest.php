<?php

declare(strict_types=1);

namespace App\Tests\SharedKernel\Functional\Web;

class SearchTest extends AbstractWebTestCase
{
    public function testSearch(): void
    {
        $this->client->request('GET', '/en/search');

        $this->assertResponseIsSuccessful();
    }
}
