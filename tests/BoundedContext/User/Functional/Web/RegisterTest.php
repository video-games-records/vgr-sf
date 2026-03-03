<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Functional\Web;

class RegisterTest extends AbstractWebFunctionalTestCase
{
    public function testShowRegisterPage(): void
    {
        $this->client->request('GET', '/en/register');

        $this->assertResponseIsSuccessful();
    }

    public function testRegisterSuccess(): void
    {
        $this->client->request('GET', '/en/register');

        $unique = uniqid();
        $this->client->submitForm('Create Account', [
            'registration_form[email]' => "newuser{$unique}@test.com",
            'registration_form[username]' => "NewUser{$unique}",
            'registration_form[plainPassword][first]' => 'SecurePass1',
            'registration_form[plainPassword][second]' => 'SecurePass1',
        ]);

        $this->assertResponseRedirects('/en/login');
    }

    public function testRegisterWithExistingEmail(): void
    {
        $this->createUser(['email' => 'existing@test.com']);

        $this->client->request('GET', '/en/register');
        $this->client->submitForm('Create Account', [
            'registration_form[email]' => 'existing@test.com',
            'registration_form[username]' => 'uniqueuser123',
            'registration_form[plainPassword][first]' => 'SecurePass1',
            'registration_form[plainPassword][second]' => 'SecurePass1',
        ]);

        // Symfony 7 returns 422 Unprocessable Content when form validation fails
        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegisterWithInvalidPassword(): void
    {
        $this->client->request('GET', '/en/register');

        $this->client->submitForm('Create Account', [
            'registration_form[email]' => 'valid@test.com',
            'registration_form[username]' => 'validuser123',
            'registration_form[plainPassword][first]' => 'short',
            'registration_form[plainPassword][second]' => 'short',
        ]);

        // Symfony 7 returns 422 Unprocessable Content when form validation fails
        $this->assertResponseStatusCodeSame(422);
    }
}
