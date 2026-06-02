<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Forum\Unit\Application\Service;

use App\BoundedContext\Forum\Application\Service\TopicReadService;
use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\Forum\Domain\Entity\ForumUserLastVisit;
use App\BoundedContext\Forum\Domain\Entity\Message;
use App\BoundedContext\Forum\Domain\Entity\Topic;
use App\BoundedContext\Forum\Domain\Entity\TopicUserLastVisit;
use App\BoundedContext\User\Domain\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class TopicReadServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private TopicReadService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->service = new TopicReadService($this->em);
    }

    private function makeUser(): User
    {
        return $this->createMock(User::class);
    }

    private function makeTopic(?Message $lastMessage = null): Topic
    {
        $forum = $this->createMock(Forum::class);
        $topic = $this->createMock(Topic::class);
        $topic->method('getForum')->willReturn($forum);
        $topic->method('getLastMessage')->willReturn($lastMessage);
        $topic->method('getId')->willReturn(42);
        return $topic;
    }

    private function makeTopicVisit(DateTime $lastVisitedAt): TopicUserLastVisit
    {
        $visit = $this->createMock(TopicUserLastVisit::class);
        $visit->method('getLastVisitedAt')->willReturn($lastVisitedAt);
        return $visit;
    }

    /**
     * @param EntityRepository<object>&MockObject $repo
     */
    private function setupGetRepository(string $class, MockObject $repo): void
    {
        $this->em->method('getRepository')
            ->willReturnCallback(function (string $requested) use ($class, $repo) {
                return $requested === $class ? $repo : $this->createMock(EntityRepository::class);
            });
    }

    public function testMarkTopicAsReadWhenAlreadyReadReturnsEarlyWithoutPersist(): void
    {
        $user = $this->makeUser();
        $lastMessage = $this->createMock(Message::class);
        $lastMessage->method('getCreatedAt')->willReturn(new DateTime('2024-01-01 10:00:00'));

        $topic = $this->makeTopic($lastMessage);

        $visit = $this->makeTopicVisit(new DateTime('2024-01-01 11:00:00'));

        $topicRepo = $this->createMock(EntityRepository::class);
        $topicRepo->method('findOneBy')->willReturn($visit);

        $this->setupGetRepository(TopicUserLastVisit::class, $topicRepo);

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $result = $this->service->markTopicAsRead($user, $topic);

        $this->assertFalse($result['topicMarkedAsRead']);
        $this->assertFalse($result['forumMarkedAsRead']);
        $this->assertTrue($result['wasAlreadyRead']);
    }

    public function testMarkTopicAsReadWithNoLastMessageIsNotConsideredRead(): void
    {
        $user = $this->makeUser();
        $topic = $this->makeTopic(null);

        $visit = $this->makeTopicVisit(new DateTime('2024-01-01 11:00:00'));

        $topicRepo = $this->createMock(EntityRepository::class);
        $topicRepo->method('findOneBy')->willReturn($visit);

        $this->em->method('getRepository')->willReturn($topicRepo);
        $this->em->method('createQueryBuilder')->will($this->throwException(new \RuntimeException('DB unavailable')));

        $result = $this->service->markTopicAsRead($user, $topic, flush: false);

        $this->assertTrue($result['topicMarkedAsRead']);
        $this->assertFalse($result['wasAlreadyRead']);
    }

    public function testMarkTopicAsReadWithNoExistingVisitCreatesNewVisit(): void
    {
        $user = $this->makeUser();
        $topic = $this->makeTopic(null);

        $topicRepo = $this->createMock(EntityRepository::class);
        $topicRepo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturn($topicRepo);
        $this->em->method('createQueryBuilder')->will($this->throwException(new \RuntimeException('DB unavailable')));

        $this->em->expects($this->atLeast(1))->method('persist');

        $result = $this->service->markTopicAsRead($user, $topic, flush: false);

        $this->assertTrue($result['topicMarkedAsRead']);
        $this->assertFalse($result['wasAlreadyRead']);
    }

    public function testMarkTopicAsReadWhenAllTopicsReadMarksForumAsRead(): void
    {
        $user = $this->makeUser();
        $topic = $this->makeTopic(null);

        $topicRepo = $this->createMock(EntityRepository::class);
        $topicRepo->method('findOneBy')->willReturn(null);

        $forumVisitRepo = $this->createMock(EntityRepository::class);
        $forumVisitRepo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturn($topicRepo);

        // createQueryBuilder throws → countUnreadTopicsInForum catches and returns 0
        $this->em->method('createQueryBuilder')->will($this->throwException(new \RuntimeException('DB unavailable')));

        $result = $this->service->markTopicAsRead($user, $topic, flush: false);

        $this->assertTrue($result['topicMarkedAsRead']);
        $this->assertTrue($result['forumMarkedAsRead']);
    }

    public function testMarkTopicAsReadWithFlushCallsFlush(): void
    {
        $user = $this->makeUser();
        $topic = $this->makeTopic(null);

        $topicRepo = $this->createMock(EntityRepository::class);
        $topicRepo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturn($topicRepo);
        $this->em->method('createQueryBuilder')->will($this->throwException(new \RuntimeException()));

        $this->em->expects($this->once())->method('flush');

        $this->service->markTopicAsRead($user, $topic, flush: true);
    }

    public function testMarkTopicAsReadWithFlushFalseDoesNotCallFlush(): void
    {
        $user = $this->makeUser();
        $topic = $this->makeTopic(null);

        $topicRepo = $this->createMock(EntityRepository::class);
        $topicRepo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturn($topicRepo);
        $this->em->method('createQueryBuilder')->will($this->throwException(new \RuntimeException()));

        $this->em->expects($this->never())->method('flush');

        $this->service->markTopicAsRead($user, $topic, flush: false);
    }
}
