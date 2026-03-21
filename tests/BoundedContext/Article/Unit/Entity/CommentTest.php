<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Article\Unit\Entity;

use App\BoundedContext\Article\Domain\Entity\Article;
use App\BoundedContext\Article\Domain\Entity\Comment;
use App\BoundedContext\User\Domain\Entity\User;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    private Comment $comment;

    protected function setUp(): void
    {
        $this->comment = new Comment();
    }

    // ------------------------------------------------------------------
    // Default values
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->comment->getId());
    }

    // ------------------------------------------------------------------
    // Article relation
    // ------------------------------------------------------------------

    public function testSetAndGetArticle(): void
    {
        $article = $this->createMock(Article::class);
        $result = $this->comment->setArticle($article);
        $this->assertSame($article, $this->comment->getArticle());
        $this->assertSame($this->comment, $result);
    }

    // ------------------------------------------------------------------
    // User relation
    // ------------------------------------------------------------------

    public function testSetAndGetUser(): void
    {
        $user = $this->createMock(User::class);
        $result = $this->comment->setUser($user);
        $this->assertSame($user, $this->comment->getUser());
        $this->assertSame($this->comment, $result);
    }

    // ------------------------------------------------------------------
    // Content getter / setter
    // ------------------------------------------------------------------

    public function testSetAndGetContent(): void
    {
        $result = $this->comment->setContent('This is a comment.');
        $this->assertSame('This is a comment.', $this->comment->getContent());
        $this->assertSame($this->comment, $result);
    }

    public function testSetContentOverwritesPreviousValue(): void
    {
        $this->comment->setContent('First content');
        $this->comment->setContent('Updated content');
        $this->assertSame('Updated content', $this->comment->getContent());
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToStringContainsComment(): void
    {
        $result = (string) $this->comment;
        $this->assertStringContainsString('comment', $result);
    }
}
