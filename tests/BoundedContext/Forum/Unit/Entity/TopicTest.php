<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Forum\Unit\Entity;

use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\Forum\Domain\Entity\Message;
use App\BoundedContext\Forum\Domain\Entity\Topic;
use App\BoundedContext\Forum\Domain\Entity\TopicType;
use App\BoundedContext\Forum\Domain\Entity\TopicUserLastVisit;
use App\BoundedContext\User\Domain\Entity\User;
use DateTime;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class TopicTest extends TestCase
{
    private Topic $topic;

    protected function setUp(): void
    {
        $this->topic = new Topic();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyCollections(): void
    {
        $topic = new Topic();

        $this->assertInstanceOf(Collection::class, $topic->getMessages());
        $this->assertCount(0, $topic->getMessages());
    }

    public function testConstructorInitializesNullablePublicProperties(): void
    {
        $topic = new Topic();
        $this->assertNull($topic->hasNewContent);
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->topic->getId());
    }

    public function testSetAndGetId(): void
    {
        $this->topic->setId(99);
        $this->assertSame(99, $this->topic->getId());
    }

    public function testSetIdReturnsSelf(): void
    {
        $result = $this->topic->setId(1);
        $this->assertSame($this->topic, $result);
    }

    public function testSetAndGetName(): void
    {
        $this->topic->setName('My Topic');
        $this->assertSame('My Topic', $this->topic->getName());
    }

    public function testSetNameReturnsSelf(): void
    {
        $result = $this->topic->setName('Test');
        $this->assertSame($this->topic, $result);
    }

    public function testNbMessageDefaultsToZero(): void
    {
        $this->assertSame(0, $this->topic->getNbMessage());
    }

    public function testSetAndGetNbMessage(): void
    {
        $this->topic->setNbMessage(15);
        $this->assertSame(15, $this->topic->getNbMessage());
    }

    public function testSetNbMessageReturnsSelf(): void
    {
        $result = $this->topic->setNbMessage(0);
        $this->assertSame($this->topic, $result);
    }

    public function testBoolArchiveDefaultsToFalse(): void
    {
        $this->assertFalse($this->topic->getBoolArchive());
    }

    public function testSetAndGetBoolArchive(): void
    {
        $this->topic->setBoolArchive(true);
        $this->assertTrue($this->topic->getBoolArchive());
    }

    public function testSetBoolArchiveReturnsSelf(): void
    {
        $result = $this->topic->setBoolArchive(false);
        $this->assertSame($this->topic, $result);
    }

    // ------------------------------------------------------------------
    // Forum relation
    // ------------------------------------------------------------------

    public function testSetAndGetForum(): void
    {
        $forum = $this->createMock(Forum::class);
        $this->topic->setForum($forum);
        $this->assertSame($forum, $this->topic->getForum());
    }

    public function testSetForumReturnsSelf(): void
    {
        $forum = $this->createMock(Forum::class);
        $result = $this->topic->setForum($forum);
        $this->assertSame($this->topic, $result);
    }

    // ------------------------------------------------------------------
    // User relation
    // ------------------------------------------------------------------

    public function testSetAndGetUser(): void
    {
        $user = $this->createMock(User::class);
        $this->topic->setUser($user);
        $this->assertSame($user, $this->topic->getUser());
    }

    public function testSetUserReturnsSelf(): void
    {
        $user = $this->createMock(User::class);
        $result = $this->topic->setUser($user);
        $this->assertSame($this->topic, $result);
    }

    // ------------------------------------------------------------------
    // TopicType relation
    // ------------------------------------------------------------------

    public function testTypeDefaultsToNull(): void
    {
        $this->assertNull($this->topic->getType());
    }

    public function testSetAndGetType(): void
    {
        $type = $this->createMock(TopicType::class);
        $this->topic->setType($type);
        $this->assertSame($type, $this->topic->getType());
    }

    public function testSetTypeToNull(): void
    {
        $type = $this->createMock(TopicType::class);
        $this->topic->setType($type);
        $this->topic->setType(null);
        $this->assertNull($this->topic->getType());
    }

    public function testSetTypeReturnsSelf(): void
    {
        $result = $this->topic->setType(null);
        $this->assertSame($this->topic, $result);
    }

    // ------------------------------------------------------------------
    // LastMessage relation
    // ------------------------------------------------------------------

    public function testLastMessageDefaultsToNull(): void
    {
        $this->assertNull($this->topic->getLastMessage());
    }

    public function testSetAndGetLastMessage(): void
    {
        $message = $this->createMock(Message::class);
        $this->topic->setLastMessage($message);
        $this->assertSame($message, $this->topic->getLastMessage());
    }

    public function testSetLastMessageToNull(): void
    {
        $message = $this->createMock(Message::class);
        $this->topic->setLastMessage($message);
        $this->topic->setLastMessage(null);
        $this->assertNull($this->topic->getLastMessage());
    }

    public function testSetLastMessageReturnsSelf(): void
    {
        $result = $this->topic->setLastMessage(null);
        $this->assertSame($this->topic, $result);
    }

    // ------------------------------------------------------------------
    // Messages collection
    // ------------------------------------------------------------------

    public function testAddMessageAddsToCollection(): void
    {
        $message = $this->createMock(Message::class);
        $message->expects($this->once())->method('setTopic')->with($this->topic);

        $this->topic->addMessage($message);

        $this->assertCount(1, $this->topic->getMessages());
    }

    public function testAddMessageSetsTopicOnMessage(): void
    {
        $message = new Message();
        $this->topic->addMessage($message);

        $this->assertSame($this->topic, $message->getTopic());
    }

    public function testSetMessagesAddsAllMessages(): void
    {
        $message1 = new Message();
        $message2 = new Message();

        $this->topic->setMessages([$message1, $message2]);

        $this->assertCount(2, $this->topic->getMessages());
    }

    // ------------------------------------------------------------------
    // getLastVisitData / getHasBeenVisited
    // ------------------------------------------------------------------

    public function testGetLastVisitDataReturnsNullWhenEmpty(): void
    {
        $this->assertNull($this->topic->getLastVisitData());
    }

    public function testGetHasBeenVisitedReturnsFalseWhenNoVisit(): void
    {
        $this->assertFalse($this->topic->getHasBeenVisited());
    }

    public function testGetLastVisitedAtReturnsNullWhenNoVisit(): void
    {
        $this->assertNull($this->topic->getLastVisitedAt());
    }

    // ------------------------------------------------------------------
    // getIsRead / hasNewContent (no visit, no last message)
    // ------------------------------------------------------------------

    public function testGetIsReadReturnsTrueWhenNoLastMessage(): void
    {
        $this->assertTrue($this->topic->getIsRead());
    }

    public function testHasNewContentReturnsFalseWhenNoLastMessage(): void
    {
        $this->assertFalse($this->topic->hasNewContent());
    }

    public function testHasNewContentReturnsTrueWhenLastMessageExistsAndNoVisit(): void
    {
        $message = $this->createMock(Message::class);
        $this->topic->setLastMessage($message);
        $this->assertTrue($this->topic->hasNewContent());
    }

    // ------------------------------------------------------------------
    // getIsNotify
    // ------------------------------------------------------------------

    public function testGetIsNotifyReturnsFalsyWhenNoVisit(): void
    {
        $this->assertFalse((bool) $this->topic->getIsNotify());
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToStringContainsName(): void
    {
        $this->topic->setName('My Topic');
        $this->assertStringContainsString('My Topic', (string) $this->topic);
    }
}
