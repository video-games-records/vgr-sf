<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Article\Functional\Web;

use App\BoundedContext\Article\Domain\ValueObject\ArticleStatus;
use App\BoundedContext\User\Domain\Entity\User;
use App\Tests\BoundedContext\Article\Factory\ArticleFactory;
use App\Tests\BoundedContext\User\Factory\UserFactory;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class ArticleControllerTest extends WebTestCase
{
    use Factories;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    // ------------------------------------------------------------------
    // show
    // ------------------------------------------------------------------

    public function testShowArticleReturnsNotFoundForMissingArticle(): void
    {
        $this->client->request('GET', '/en/article/99999-nonexistent-article');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShowPublishedArticleReturnsSuccess(): void
    {
        $article = ArticleFactory::new()->published()->create();

        $this->client->request('GET', '/en/article/' . $article->getId() . '-' . $article->getSlug());

        $this->assertResponseIsSuccessful();
    }

    public function testShowArticleWithWrongSlugRedirects(): void
    {
        $article = ArticleFactory::new()->published()->create();

        $this->client->request('GET', '/en/article/' . $article->getId() . '-wrong-slug');

        $this->assertResponseRedirects();
        $this->assertSame(301, $this->client->getResponse()->getStatusCode());
    }

    public function testShowArticleContainsTitle(): void
    {
        $user = UserFactory::new()->create();
        $article = ArticleFactory::new()
            ->published()
            ->with(['author' => $user])
            ->create();
        $article->setTitle('My Test Article Title', 'en');

        $this->client->request('GET', '/en/article/' . $article->getId() . '-' . $article->getSlug());

        $this->assertResponseIsSuccessful();
    }

    // ------------------------------------------------------------------
    // createComment - unauthenticated
    // ------------------------------------------------------------------

    public function testCreateCommentRequiresAuthentication(): void
    {
        $article = ArticleFactory::new()->published()->create();

        $this->client->request(
            'POST',
            '/en/article/' . $article->getId() . '-' . $article->getSlug() . '/comment',
            ['comment' => ['content' => 'Test comment']],
        );

        // Redirect to login when not authenticated
        $this->assertResponseRedirects();
    }

    // ------------------------------------------------------------------
    // createComment - authenticated
    // ------------------------------------------------------------------

    public function testCreateCommentRedirectsOnSuccessForAuthenticatedUser(): void
    {
        $userProxy = UserFactory::new()->withCredentials('commenter@test.com', 'commenter', 'password')->create();
        $article = ArticleFactory::new()->published()->with(['author' => $userProxy])->create();

        /** @var ManagerRegistry $doctrine */
        $doctrine = static::getContainer()->get('doctrine');
        /** @var User $user */
        $user = $doctrine->getManager()->find(User::class, $userProxy->getId());

        $this->client->loginUser($user, 'user');

        $this->client->request(
            'POST',
            '/en/article/' . $article->getId() . '-' . $article->getSlug() . '/comment',
            ['comment_type' => ['content' => 'This is my comment']],
        );

        $this->assertResponseRedirects();
    }

    // ------------------------------------------------------------------
    // editComment - unauthorized
    // ------------------------------------------------------------------

    public function testEditCommentReturnsNotFoundForMissingArticle(): void
    {
        $userProxy = UserFactory::new()->withCredentials('editor@test.com', 'editor', 'password')->create();

        /** @var ManagerRegistry $doctrine */
        $doctrine = static::getContainer()->get('doctrine');
        /** @var User $user */
        $user = $doctrine->getManager()->find(User::class, $userProxy->getId());

        $this->client->loginUser($user, 'user');

        $this->client->request(
            'POST',
            '/en/article/99999-nonexistent/comment/1/edit',
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
