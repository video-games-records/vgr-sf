<?php

declare(strict_types=1);

namespace App\Tests\SharedKernel\Functional\Web;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

abstract class AbstractWebTestCase extends WebTestCase
{
    use Factories;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }
}
