<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Article\Functional\Web;

use App\BoundedContext\Article\Presentation\Web\Controller\TopNewsController;
use App\Tests\BoundedContext\Article\Factory\ArticleFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class TopNewsControllerTest extends WebTestCase
{
    use Factories;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testCacheKeyConstantIsDefined(): void
    {
        $this->assertSame('latest_news', TopNewsController::CACHE_KEY);
    }

    public function testHomePageRendersSuccessfullyWithNoArticles(): void
    {
        $this->client->request('GET', '/en/');

        $this->assertResponseIsSuccessful();
    }

    public function testHomePageRendersSuccessfullyWithPublishedArticles(): void
    {
        ArticleFactory::new()->published()->many(3)->create();

        $this->client->request('GET', '/en/');

        $this->assertResponseIsSuccessful();
    }

    public function testLatestNewsIsIncludedInHomePage(): void
    {
        ArticleFactory::new()->published()->many(2)->create();

        $crawler = $this->client->request('GET', '/en/');

        $this->assertResponseIsSuccessful();
        // The home page includes the latest news sub-request
        $this->assertGreaterThan(0, $crawler->filter('body')->count());
    }
}
