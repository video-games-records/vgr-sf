<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\Client;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Zenstruck\Foundry\Test\Factories;

class AbstractFunctionalTestCase extends ApiTestCase
{
    use Factories;

    protected Client $apiClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiClient = static::createClient();
    }
}
