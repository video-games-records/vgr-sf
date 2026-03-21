<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Forum\Unit\Entity;

use App\BoundedContext\Forum\Domain\Entity\Category;
use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\Forum\Domain\Entity\ForumUserLastVisit;
use App\BoundedContext\Forum\Domain\Entity\Message;
use App\BoundedContext\Forum\Domain\ValueObject\ForumStatus;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class ForumTest extends TestCase
{
    private Forum $forum;

    protected function setUp(): void
    {
        $this->forum = new Forum();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyCollections(): void
    {
        $forum = new Forum();

        $this->assertInstanceOf(Collection::class, $forum->getTopics());
        $this->assertCount(0, $forum->getTopics());
    }

    public function testConstructorInitializesNullablePublicProperties(): void
    {
        $forum = new Forum();

        $this->assertNull($forum->unreadTopicsCount);
        $this->assertNull($forum->isUnread);
        $this->assertNull($forum->hasNewContent);
        $this->assertNull($forum->hasBeenVisited);
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->forum->getId());
    }

    public function testSetAndGetLibForum(): void
    {
        $this->forum->setLibForum('General Discussion');
        $this->assertSame('General Discussion', $this->forum->getLibForum());
    }

    public function testSetLibForumReturnsSelf(): void
    {
        $result = $this->forum->setLibForum('General');
        $this->assertSame($this->forum, $result);
    }

    public function testLibForumFrDefaultsToNull(): void
    {
        $this->assertNull($this->forum->getLibForumFr());
    }

    public function testSetAndGetLibForumFr(): void
    {
        $this->forum->setLibForumFr('Discussion Générale');
        $this->assertSame('Discussion Générale', $this->forum->getLibForumFr());
    }

    public function testSetLibForumFrReturnsSelf(): void
    {
        $result = $this->forum->setLibForumFr('Test');
        $this->assertSame($this->forum, $result);
    }

    public function testPositionDefaultsToZero(): void
    {
        $this->assertSame(0, $this->forum->getPosition());
    }

    public function testSetAndGetPosition(): void
    {
        $this->forum->setPosition(5);
        $this->assertSame(5, $this->forum->getPosition());
    }

    public function testSetPositionReturnsSelf(): void
    {
        $result = $this->forum->setPosition(1);
        $this->assertSame($this->forum, $result);
    }

    public function testStatusDefaultsToPublic(): void
    {
        $this->assertSame(ForumStatus::PUBLIC, $this->forum->getStatus());
    }

    public function testSetAndGetStatus(): void
    {
        $this->forum->setStatus(ForumStatus::PRIVATE);
        $this->assertSame(ForumStatus::PRIVATE, $this->forum->getStatus());
    }

    public function testSetStatusReturnsSelf(): void
    {
        $result = $this->forum->setStatus(ForumStatus::PUBLIC);
        $this->assertSame($this->forum, $result);
    }

    public function testRoleDefaultsToNull(): void
    {
        $this->assertNull($this->forum->getRole());
    }

    public function testSetAndGetRole(): void
    {
        $this->forum->setRole('ROLE_ADMIN');
        $this->assertSame('ROLE_ADMIN', $this->forum->getRole());
    }

    public function testSetRoleToNull(): void
    {
        $this->forum->setRole('ROLE_ADMIN');
        $this->forum->setRole(null);
        $this->assertNull($this->forum->getRole());
    }

    public function testSetRoleReturnsSelf(): void
    {
        $result = $this->forum->setRole(null);
        $this->assertSame($this->forum, $result);
    }

    public function testNbMessageDefaultsToZero(): void
    {
        $this->assertSame(0, $this->forum->getNbMessage());
    }

    public function testSetAndGetNbMessage(): void
    {
        $this->forum->setNbMessage(42);
        $this->assertSame(42, $this->forum->getNbMessage());
    }

    public function testSetNbMessageReturnsSelf(): void
    {
        $result = $this->forum->setNbMessage(0);
        $this->assertSame($this->forum, $result);
    }

    public function testNbTopicDefaultsToZero(): void
    {
        $this->assertSame(0, $this->forum->getNbTopic());
    }

    public function testSetAndGetNbTopic(): void
    {
        $this->forum->setNbTopic(10);
        $this->assertSame(10, $this->forum->getNbTopic());
    }

    public function testSetNbTopicReturnsSelf(): void
    {
        $result = $this->forum->setNbTopic(0);
        $this->assertSame($this->forum, $result);
    }

    // ------------------------------------------------------------------
    // Category relation
    // ------------------------------------------------------------------

    public function testCategoryDefaultsToNull(): void
    {
        $this->assertNull($this->forum->getCategory());
    }

    public function testSetAndGetCategory(): void
    {
        $category = $this->createMock(Category::class);
        $this->forum->setCategory($category);
        $this->assertSame($category, $this->forum->getCategory());
    }

    public function testSetCategoryToNull(): void
    {
        $category = $this->createMock(Category::class);
        $this->forum->setCategory($category);
        $this->forum->setCategory(null);
        $this->assertNull($this->forum->getCategory());
    }

    public function testSetCategoryReturnsSelf(): void
    {
        $result = $this->forum->setCategory(null);
        $this->assertSame($this->forum, $result);
    }

    // ------------------------------------------------------------------
    // LastMessage relation
    // ------------------------------------------------------------------

    public function testLastMessageDefaultsToNull(): void
    {
        $this->assertNull($this->forum->getLastMessage());
    }

    public function testSetAndGetLastMessage(): void
    {
        $message = $this->createMock(Message::class);
        $this->forum->setLastMessage($message);
        $this->assertSame($message, $this->forum->getLastMessage());
    }

    public function testSetLastMessageToNull(): void
    {
        $message = $this->createMock(Message::class);
        $this->forum->setLastMessage($message);
        $this->forum->setLastMessage(null);
        $this->assertNull($this->forum->getLastMessage());
    }

    public function testSetLastMessageReturnsSelf(): void
    {
        $result = $this->forum->setLastMessage(null);
        $this->assertSame($this->forum, $result);
    }

    // ------------------------------------------------------------------
    // getHasBeenVisited
    // ------------------------------------------------------------------

    public function testGetHasBeenVisitedReturnsFalseWhenNoVisit(): void
    {
        $this->assertFalse($this->forum->getHasBeenVisited());
    }

    // ------------------------------------------------------------------
    // getLastVisitedAt
    // ------------------------------------------------------------------

    public function testGetLastVisitedAtReturnsNullWhenNoVisit(): void
    {
        $this->assertNull($this->forum->getLastVisitedAt());
    }

    // ------------------------------------------------------------------
    // getHasNewContent
    // ------------------------------------------------------------------

    public function testGetHasNewContentReturnsFalseWhenNoLastMessage(): void
    {
        $this->assertFalse($this->forum->getHasNewContent());
    }

    public function testGetHasNewContentReturnsTrueWhenLastMessageExistsAndNoVisit(): void
    {
        $message = $this->createMock(Message::class);
        $this->forum->setLastMessage($message);
        $this->assertTrue($this->forum->getHasNewContent());
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToStringContainsLibForum(): void
    {
        $this->forum->setLibForum('News');
        $this->assertStringContainsString('News', (string) $this->forum);
    }
}
