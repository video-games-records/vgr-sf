<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Functional\Web;

use App\BoundedContext\User\Domain\Entity\User;
use App\Tests\BoundedContext\User\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

abstract class AbstractWebFunctionalTestCase extends WebTestCase
{
    use Factories;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    /**
     * Creates a regular user for testing
     * @param array<string, mixed> $overrides
     */
    protected function createUser(array $overrides = []): User
    {
        $unique = uniqid();
        $username = $overrides['username'] ?? "testuser{$unique}";

        return UserFactory::new()
            ->withCredentials(
                $overrides['email'] ?? "{$username}@test.com",
                $username,
                $overrides['password'] ?? 'password'
            )
            ->create();
    }
}
