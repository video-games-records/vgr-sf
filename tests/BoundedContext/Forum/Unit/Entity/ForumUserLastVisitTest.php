<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Forum\Unit\Entity;

use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\Forum\Domain\Entity\ForumUserLastVisit;
use App\BoundedContext\User\Domain\Entity\User;
use DateTime;
use PHPUnit\Framework\TestCase;

class ForumUserLastVisitTest extends TestCase
{
    private ForumUserLastVisit $forumUserLastVisit;

    protected function setUp(): void
    {
        $this->forumUserLastVisit = new ForumUserLastVisit();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorSetsLastVisitedAtToNow(): void
    {
        $before = new DateTime();
        $visit = new ForumUserLastVisit();
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
        $this->forumUserLastVisit->setUser($user);
        $this->assertSame($user, $this->forumUserLastVisit->getUser());
    }

    public function testSetUserReturnsSelf(): void
    {
        $user = $this->createMock(User::class);
        $result = $this->forumUserLastVisit->setUser($user);
        $this->assertSame($this->forumUserLastVisit, $result);
    }

    // ------------------------------------------------------------------
    // Forum relation
    // ------------------------------------------------------------------

    public function testSetAndGetForum(): void
    {
        $forum = $this->createMock(Forum::class);
        $this->forumUserLastVisit->setForum($forum);
        $this->assertSame($forum, $this->forumUserLastVisit->getForum());
    }

    public function testSetForumReturnsSelf(): void
    {
        $forum = $this->createMock(Forum::class);
        $result = $this->forumUserLastVisit->setForum($forum);
        $this->assertSame($this->forumUserLastVisit, $result);
    }

    // ------------------------------------------------------------------
    // LastVisitedAt
    // ------------------------------------------------------------------

    public function testSetAndGetLastVisitedAt(): void
    {
        $date = new DateTime('2025-06-15 10:00:00');
        $this->forumUserLastVisit->setLastVisitedAt($date);
        $this->assertSame($date, $this->forumUserLastVisit->getLastVisitedAt());
    }

    public function testSetLastVisitedAtReturnsSelf(): void
    {
        $result = $this->forumUserLastVisit->setLastVisitedAt(new DateTime());
        $this->assertSame($this->forumUserLastVisit, $result);
    }

    // ------------------------------------------------------------------
    // updateLastVisit
    // ------------------------------------------------------------------

    public function testUpdateLastVisitSetsCurrentTime(): void
    {
        $oldDate = new DateTime('2020-01-01');
        $this->forumUserLastVisit->setLastVisitedAt($oldDate);

        $before = new DateTime();
        $this->forumUserLastVisit->updateLastVisit();
        $after = new DateTime();

        $this->assertGreaterThanOrEqual($before, $this->forumUserLastVisit->getLastVisitedAt());
        $this->assertLessThanOrEqual($after, $this->forumUserLastVisit->getLastVisitedAt());
    }

    // ------------------------------------------------------------------
    // wasVisitedAfter
    // ------------------------------------------------------------------

    public function testWasVisitedAfterReturnsTrueWhenLastVisitIsNewer(): void
    {
        $this->forumUserLastVisit->setLastVisitedAt(new DateTime('2025-06-15'));
        $this->assertTrue($this->forumUserLastVisit->wasVisitedAfter(new DateTime('2025-06-01')));
    }

    public function testWasVisitedAfterReturnsFalseWhenLastVisitIsOlder(): void
    {
        $this->forumUserLastVisit->setLastVisitedAt(new DateTime('2025-06-01'));
        $this->assertFalse($this->forumUserLastVisit->wasVisitedAfter(new DateTime('2025-06-15')));
    }
}
