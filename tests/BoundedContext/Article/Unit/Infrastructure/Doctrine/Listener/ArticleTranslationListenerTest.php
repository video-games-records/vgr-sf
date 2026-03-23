<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Article\Unit\Infrastructure\Doctrine\Listener;

use App\BoundedContext\Article\Domain\Entity\Article;
use App\BoundedContext\Article\Domain\Entity\ArticleTranslation;
use App\BoundedContext\Article\Infrastructure\Doctrine\Listener\ArticleTranslationListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ArticleTranslationListenerTest extends TestCase
{
    private ArticleTranslationListener $listener;

    protected function setUp(): void
    {
        $this->listener = new ArticleTranslationListener();
    }

    public function testPostUpdateSetsUpdatedAtOnArticle(): void
    {
        $article = new Article();
        $article->setTitle('Test', 'en');
        $article->setSlug('test');

        $translation = new ArticleTranslation();
        $translation->setTranslatable($article);
        $translation->setLocale('en');
        $translation->setTitle('Test');
        $translation->setContent('Content');

        /** @var EntityManagerInterface&MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($article);

        $event = new PostUpdateEventArgs($translation, $em);

        $beforeUpdate = new \DateTime();
        $this->listener->postUpdate($translation, $event);

        $this->assertGreaterThanOrEqual($beforeUpdate, $article->getUpdatedAt());
    }

    public function testPostUpdatePersistsArticleWithNewTimestamp(): void
    {
        $article = new Article();
        $article->setTitle('Article', 'en');
        $article->setSlug('article');

        $translation = new ArticleTranslation();
        $translation->setTranslatable($article);
        $translation->setLocale('fr');
        $translation->setTitle('Article FR');
        $translation->setContent('Contenu');

        /** @var EntityManagerInterface&MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);

        $persistedObjects = [];
        $em->method('persist')->willReturnCallback(function (object $obj) use (&$persistedObjects): void {
            $persistedObjects[] = $obj;
        });

        $event = new PostUpdateEventArgs($translation, $em);

        $this->listener->postUpdate($translation, $event);

        $this->assertContains($article, $persistedObjects);
        $this->assertGreaterThanOrEqual(new \DateTime('-1 second'), $article->getUpdatedAt());
    }

    public function testPostUpdateAlwaysSetsANewTimestamp(): void
    {
        $article = new Article();
        $article->setTitle('Article', 'en');
        $article->setSlug('article');

        $translation = new ArticleTranslation();
        $translation->setTranslatable($article);
        $translation->setLocale('en');
        $translation->setTitle('Updated Title');
        $translation->setContent('Updated Content');

        /** @var EntityManagerInterface&MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('persist');

        $event = new PostUpdateEventArgs($translation, $em);

        $start = new \DateTime();
        $this->listener->postUpdate($translation, $event);
        $end = new \DateTime();

        $updatedAt = $article->getUpdatedAt();
        $this->assertGreaterThanOrEqual($start, $updatedAt);
        $this->assertLessThanOrEqual($end, $updatedAt);
    }
}
