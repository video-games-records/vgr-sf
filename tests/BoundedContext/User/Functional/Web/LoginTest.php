<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Functional\Web;

class LoginTest extends AbstractWebFunctionalTestCase
{
    public function testShowLoginPage(): void
    {
        $this->client->request('GET', '/en/login');

        $this->assertResponseIsSuccessful();
    }

    public function testLoginSuccess(): void
    {
        $user = $this->createUser();

        $crawler = $this->client->request('GET', '/en/login');
        $csrfToken = $crawler->filter('input[name="_csrf_token"]')->attr('value');

        $this->client->request('POST', '/en/login', [
            '_username' => $user->getUsername(),
            '_password' => 'password',
            '_csrf_token' => $csrfToken,
        ]);

        // Successful login redirects to the configured default target path
        $this->assertResponseRedirects();
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $crawler = $this->client->request('GET', '/en/login');
        $csrfToken = $crawler->filter('input[name="_csrf_token"]')->attr('value');

        $this->client->request('POST', '/en/login', [
            '_username' => 'nonexistent@test.com',
            '_password' => 'wrongpassword',
            '_csrf_token' => $csrfToken,
        ]);

        // Failed login redirects back to the login page
        $this->assertResponseRedirects('/en/login');
    }
}
