<?php

declare(strict_types=1);

namespace App\Tests\SharedKernel\Functional\Web;

class StaticTest extends AbstractWebTestCase
{
    public function testFaq(): void
    {
        $this->client->request('GET', '/en/faq');

        $this->assertResponseIsSuccessful();
    }

    public function testRules(): void
    {
        $this->client->request('GET', '/en/rules');

        $this->assertResponseIsSuccessful();
    }

    public function testAbout(): void
    {
        $this->client->request('GET', '/en/about');

        $this->assertResponseIsSuccessful();
    }

    public function testPrivacy(): void
    {
        $this->client->request('GET', '/en/privacy');

        $this->assertResponseIsSuccessful();
    }

    public function testTerms(): void
    {
        $this->client->request('GET', '/en/terms');

        $this->assertResponseIsSuccessful();
    }

    public function testContact(): void
    {
        $this->client->request('GET', '/en/contact');

        $this->assertResponseIsSuccessful();
    }
}
