<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\Client;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Tests\Factory\UserFactory;
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

    /**
     * Helper method pour créer un utilisateur admin
     * Le Player est créé automatiquement via CreatePlayerListener
     */
    protected function createAdminUser(): User
    {
        $unique = uniqid();
        $username = "admin{$unique}";

        return UserFactory::new()
            ->asSuperAdmin()
            ->withCredentials("{$username}@test.com", $username, 'password')
            ->create();
    }

    /**
     * Helper method pour créer un utilisateur normal
     * Le Player est créé automatiquement via CreatePlayerListener
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

    /**
     * Helper method pour authentifier un utilisateur via JWT
     */
    protected function authenticateUser(object $user): void
    {
        $loginResponse = $this->apiClient->request('POST', '/api/login_check', [
            'json' => [
                'username' => $user->getUsername(),
                'password' => 'password', // Password from factory
            ]
        ]);

        $this->assertEquals(200, $loginResponse->getStatusCode());
        $token = $loginResponse->toArray()['token'];

        $this->apiClient->setDefaultOptions([
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
        ]);
    }
}
