<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Forum\Unit\Entity;

use App\BoundedContext\Forum\Domain\Entity\Message;
use App\BoundedContext\Forum\Domain\Entity\Topic;
use App\BoundedContext\Forum\Domain\Entity\TopicUserLastVisit;
use App\BoundedContext\User\Domain\Entity\User;
use DateTime;
use PHPUnit\Framework\TestCase;

class TopicUserLastVisitTest extends TestCase
{
    private TopicUserLastVisit $topicUserLastVisit;

    protected function setUp(): void
    {
        $this->topicUserLastVisit = new TopicUserLastVisit();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorSetsLastVisitedAtToNow(): void
    {
        $before = new DateTime();
        $visit = new TopicUserLastVisit();
        $after = new DateTime();

        $this->assertGreaterThanOrEqual($before, $visit->getLastVisitedAt());
        $this->assertLessThanOrEqual($after, $visit->getLastVisitedAt());
    }

    // ------------------------------------------------------------------
    // User relation
    // ------------------------------------------------------------------

    public function testSetAndGetUser(): void
    {
        $user = $this->createMock(User::class);
        $this->topicUserLastVisit->setUser($user);
        $this->assertSame($user, $this->topicUserLastVisit->getUser());
    }

    public function testSetUserReturnsSelf(): void
    {
        $user = $this->createMock(User::class);
        $result = $this->topicUserLastVisit->setUser($user);
        $this->assertSame($this->topicUserLastVisit, $result);
    }

    // ------------------------------------------------------------------
    // Topic relation
    // ------------------------------------------------------------------

    public function testSetAndGetTopic(): void
    {
        $topic = $this->createMock(Topic::class);
        $this->topicUserLastVisit->setTopic($topic);
        $this->assertSame($topic, $this->topicUserLastVisit->getTopic());
    }

    public function testSetTopicReturnsSelf(): void
    {
        $topic = $this->createMock(Topic::class);
        $result = $this->topicUserLastVisit->setTopic($topic);
        $this->assertSame($this->topicUserLastVisit, $result);
    }

    // ------------------------------------------------------------------
    // LastVisitedAt
    // ------------------------------------------------------------------

    public function testSetAndGetLastVisitedAt(): void
    {
        $date = new DateTime('2025-06-15 10:00:00');
        $this->topicUserLastVisit->setLastVisitedAt($date);
        $this->assertSame($date, $this->topicUserLastVisit->getLastVisitedAt());
    }

    public function testSetLastVisitedAtReturnsSelf(): void
    {
        $result = $this->topicUserLastVisit->setLastVisitedAt(new DateTime());
        $this->assertSame($this->topicUserLastVisit, $result);
    }

    // ------------------------------------------------------------------
    // IsNotify
    // ------------------------------------------------------------------

    public function testIsNotifyDefaultsToFalse(): void
    {
        $this->assertFalse($this->topicUserLastVisit->getIsNotify());
    }

    public function testSetAndGetIsNotify(): void
    {
        $this->topicUserLastVisit->setIsNotify(true);
        $this->assertTrue($this->topicUserLastVisit->getIsNotify());
    }

    public function testSetIsNotifyReturnsSelf(): void
    {
        $result = $this->topicUserLastVisit->setIsNotify(true);
        $this->assertSame($this->topicUserLastVisit, $result);
    }

    // ------------------------------------------------------------------
    // updateLastVisit
    // ------------------------------------------------------------------

    public function testUpdateLastVisitSetsCurrentTime(): void
    {
        $oldDate = new DateTime('2020-01-01');
        $this->topicUserLastVisit->setLastVisitedAt($oldDate);

        $before = new DateTime();
        $this->topicUserLastVisit->updateLastVisit();
        $after = new DateTime();

        $this->assertGreaterThanOrEqual($before, $this->topicUserLastVisit->getLastVisitedAt());
        $this->assertLessThanOrEqual($after, $this->topicUserLastVisit->getLastVisitedAt());
    }

    // ------------------------------------------------------------------
    // wasVisitedAfter
    // ------------------------------------------------------------------

    public function testWasVisitedAfterReturnsTrueWhenLastVisitIsNewer(): void
    {
        $this->topicUserLastVisit->setLastVisitedAt(new DateTime('2025-06-15'));
        $this->assertTrue($this->topicUserLastVisit->wasVisitedAfter(new DateTime('2025-06-01')));
    }

    public function testWasVisitedAfterReturnsFalseWhenLastVisitIsOlder(): void
    {
        $this->topicUserLastVisit->setLastVisitedAt(new DateTime('2025-06-01'));
        $this->assertFalse($this->topicUserLastVisit->wasVisitedAfter(new DateTime('2025-06-15')));
    }

    // ------------------------------------------------------------------
    // isTopicRead
    // ------------------------------------------------------------------

    public function testIsTopicReadReturnsTrueWhenTopicHasNoLastMessage(): void
    {
        $topic = $this->createMock(Topic::class);
        $topic->method('getLastMessage')->willReturn(null);

        $this->topicUserLastVisit->setTopic($topic);

        $this->assertTrue($this->topicUserLastVisit->isTopicRead());
    }

    public function testIsTopicReadReturnsTrueWhenVisitIsAfterLastMessage(): void
    {
        $lastMessage = $this->createMock(Message::class);
        $lastMessage->method('getCreatedAt')->willReturn(new DateTime('2025-06-01'));

        $topic = $this->createMock(Topic::class);
        $topic->method('getLastMessage')->willReturn($lastMessage);

        $this->topicUserLastVisit->setTopic($topic);
        $this->topicUserLastVisit->setLastVisitedAt(new DateTime('2025-06-10'));

        $this->assertTrue($this->topicUserLastVisit->isTopicRead());
    }

    public function testIsTopicReadReturnsFalseWhenVisitIsBeforeLastMessage(): void
    {
        $lastMessage = $this->createMock(Message::class);
        $lastMessage->method('getCreatedAt')->willReturn(new DateTime('2025-06-10'));

        $topic = $this->createMock(Topic::class);
        $topic->method('getLastMessage')->willReturn($lastMessage);

        $this->topicUserLastVisit->setTopic($topic);
        $this->topicUserLastVisit->setLastVisitedAt(new DateTime('2025-06-01'));

        $this->assertFalse($this->topicUserLastVisit->isTopicRead());
    }
}
