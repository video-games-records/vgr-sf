<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Forum\Unit\Entity;

use App\BoundedContext\Forum\Domain\Entity\Message;
use App\BoundedContext\Forum\Domain\Entity\Topic;
use App\BoundedContext\User\Domain\Entity\User;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    private Message $message;

    protected function setUp(): void
    {
        $this->message = new Message();
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->message->getId());
    }

    public function testSetAndGetMessage(): void
    {
        $this->message->setMessage('Hello World');
        $this->assertSame('Hello World', $this->message->getMessage());
    }

    public function testSetMessageReturnsSelf(): void
    {
        $result = $this->message->setMessage('Some text');
        $this->assertSame($this->message, $result);
    }

    public function testPositionDefaultsToOne(): void
    {
        $this->assertSame(1, $this->message->getPosition());
    }

    public function testSetAndGetPosition(): void
    {
        $this->message->setPosition(5);
        $this->assertSame(5, $this->message->getPosition());
    }

    public function testSetPositionReturnsSelf(): void
    {
        $result = $this->message->setPosition(3);
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // Topic relation
    // ------------------------------------------------------------------

    public function testSetAndGetTopic(): void
    {
        $topic = $this->createMock(Topic::class);
        $this->message->setTopic($topic);
        $this->assertSame($topic, $this->message->getTopic());
    }

    public function testSetTopicReturnsSelf(): void
    {
        $topic = $this->createMock(Topic::class);
        $result = $this->message->setTopic($topic);
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // User relation
    // ------------------------------------------------------------------

    public function testSetAndGetUser(): void
    {
        $user = $this->createMock(User::class);
        $this->message->setUser($user);
        $this->assertSame($user, $this->message->getUser());
    }

    public function testSetUserReturnsSelf(): void
    {
        $user = $this->createMock(User::class);
        $result = $this->message->setUser($user);
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // getPage
    // ------------------------------------------------------------------

    public function testGetPageReturnsOneForFirstMessage(): void
    {
        $this->message->setPosition(1);
        $this->assertSame(1, $this->message->getPage());
    }

    public function testGetPageReturnsOneForTwentiethMessage(): void
    {
        $this->message->setPosition(20);
        $this->assertSame(1, $this->message->getPage());
    }

    public function testGetPageReturnsTwoForTwentyFirstMessage(): void
    {
        $this->message->setPosition(21);
        $this->assertSame(2, $this->message->getPage());
    }

    public function testGetPageReturnsTwoForFortiethMessage(): void
    {
        $this->message->setPosition(40);
        $this->assertSame(2, $this->message->getPage());
    }

    public function testGetPageReturnsThreeForFortyFirstMessage(): void
    {
        $this->message->setPosition(41);
        $this->assertSame(3, $this->message->getPage());
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToStringContainsBrackets(): void
    {
        $this->assertStringContainsString('[', (string) $this->message);
        $this->assertStringContainsString(']', (string) $this->message);
    }
}
