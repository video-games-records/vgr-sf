<?php

declare(strict_types=1);

namespace App\Tests\SharedKernel\Functional\Web;

class HomeTest extends AbstractWebTestCase
{
    public function testHome(): void
    {
        $this->client->request('GET', '/en/');

        $this->assertResponseIsSuccessful();
    }
}
