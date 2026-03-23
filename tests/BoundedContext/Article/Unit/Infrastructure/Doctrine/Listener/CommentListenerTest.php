<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Article\Unit\Infrastructure\Doctrine\Listener;

use App\BoundedContext\Article\Domain\Entity\Article;
use App\BoundedContext\Article\Domain\Entity\Comment;
use App\BoundedContext\Article\Infrastructure\Doctrine\Listener\CommentListener;
use App\BoundedContext\User\Domain\Entity\User;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

#[AllowMockObjectsWithoutExpectations]
class CommentListenerTest extends TestCase
{
    private HtmlSanitizerInterface&MockObject $sanitizer;
    private CommentListener $listener;

    protected function setUp(): void
    {
        $this->sanitizer = $this->createMock(HtmlSanitizerInterface::class);
        $this->listener = new CommentListener($this->sanitizer);
    }

    // ------------------------------------------------------------------
    // prePersist
    // ------------------------------------------------------------------

    public function testPrePersistIncrementsArticleNbComment(): void
    {
        $article = new Article();
        $article->setNbComment(3);

        $comment = $this->buildComment($article, 'Some content');

        $this->sanitizer->method('sanitize')->willReturnArgument(0);
        $this->listener->prePersist($comment);

        $this->assertSame(4, $article->getNbComment());
    }

    public function testPrePersistSanitizesContent(): void
    {
        $article = new Article();
        $comment = $this->buildComment($article, '<script>alert("xss")</script>Hello');

        $this->sanitizer->expects($this->once())
            ->method('sanitize')
            ->with('<script>alert("xss")</script>Hello')
            ->willReturn('Hello');

        $this->listener->prePersist($comment);

        $this->assertSame('Hello', $comment->getContent());
    }

    public function testPrePersistIncrementStartingFromZero(): void
    {
        $article = new Article();
        $this->assertSame(0, $article->getNbComment());

        $comment = $this->buildComment($article, 'First comment');
        $this->sanitizer->method('sanitize')->willReturnArgument(0);
        $this->listener->prePersist($comment);

        $this->assertSame(1, $article->getNbComment());
    }

    // ------------------------------------------------------------------
    // preUpdate
    // ------------------------------------------------------------------

    public function testPreUpdateSanitizesContent(): void
    {
        $article = new Article();
        $comment = $this->buildComment($article, '<b>Bold</b> text <script>evil()</script>');

        $this->sanitizer->expects($this->once())
            ->method('sanitize')
            ->with('<b>Bold</b> text <script>evil()</script>')
            ->willReturn('<b>Bold</b> text ');

        $this->listener->preUpdate($comment);

        $this->assertSame('<b>Bold</b> text ', $comment->getContent());
    }

    public function testPreUpdateDoesNotChangeNbComment(): void
    {
        $article = new Article();
        $article->setNbComment(5);
        $comment = $this->buildComment($article, 'Updated content');

        $this->sanitizer->method('sanitize')->willReturnArgument(0);
        $this->listener->preUpdate($comment);

        $this->assertSame(5, $article->getNbComment());
    }

    // ------------------------------------------------------------------
    // preRemove
    // ------------------------------------------------------------------

    public function testPreRemoveDecrementsArticleNbComment(): void
    {
        $article = new Article();
        $article->setNbComment(3);

        $comment = $this->buildComment($article, 'Content to remove');

        $this->listener->preRemove($comment);

        $this->assertSame(2, $article->getNbComment());
    }

    public function testPreRemoveDoesNotSanitizeContent(): void
    {
        $article = new Article();
        $article->setNbComment(1);
        $comment = $this->buildComment($article, 'Content');

        $this->sanitizer->expects($this->never())->method('sanitize');
        $this->listener->preRemove($comment);
    }

    // ------------------------------------------------------------------
    // Helper
    // ------------------------------------------------------------------

    private function buildComment(Article $article, string $content): Comment
    {
        $user = $this->createMock(User::class);

        $comment = new Comment();
        $comment->setArticle($article);
        $comment->setUser($user);
        $comment->setContent($content);

        return $comment;
    }
}
