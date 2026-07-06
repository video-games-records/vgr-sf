<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Forum\Unit\Infrastructure\Security\Voter;

use App\BoundedContext\Forum\Domain\Entity\Topic;
use App\BoundedContext\Forum\Infrastructure\Security\Voter\TopicVoter;
use App\BoundedContext\User\Domain\Entity\User;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[AllowMockObjectsWithoutExpectations]
class TopicVoterTest extends TestCase
{
    private TopicVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new TopicVoter();
    }

    private function makeToken(?UserInterface $user, string ...$roles): TokenInterface&MockObject
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $token->method('getRoleNames')->willReturn($roles);
        return $token;
    }

    private function makeTopic(int $userId): Topic&MockObject
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($userId);

        $topic = $this->createMock(Topic::class);
        $topic->method('getUser')->willReturn($user);
        return $topic;
    }

    public function testAbstainsOnWrongAttribute(): void
    {
        $topic = $this->createMock(Topic::class);
        $token = $this->makeToken(null);

        $result = $this->voter->vote($token, $topic, ['WRONG_ATTR']);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testAbstainsOnWrongSubject(): void
    {
        $token = $this->makeToken(null);

        $result = $this->voter->vote($token, new \stdClass(), [TopicVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testDeniesWhenTokenHasNoUser(): void
    {
        $topic = $this->makeTopic(1);
        $token = $this->makeToken(null);

        $result = $this->voter->vote($token, $topic, [TopicVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testDeniesWhenTokenUserIsNotUserEntityInstance(): void
    {
        $topic = $this->makeTopic(1);
        $otherUser = $this->createMock(UserInterface::class);
        $token = $this->makeToken($otherUser);

        $result = $this->voter->vote($token, $topic, [TopicVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testGrantsWhenUserOwnsTopic(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(42);

        $topic = $this->makeTopic(42);
        $token = $this->makeToken($user, 'ROLE_USER');

        $result = $this->voter->vote($token, $topic, [TopicVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testDeniesWhenUserDoesNotOwnTopicAndIsNotAdmin(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(99);

        $topic = $this->makeTopic(42);
        $token = $this->makeToken($user, 'ROLE_USER');

        $result = $this->voter->vote($token, $topic, [TopicVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testGrantsWhenUserIsAdminEvenIfNotOwner(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(99);

        $topic = $this->makeTopic(42);
        $token = $this->makeToken($user, 'ROLE_USER', 'ROLE_ADMIN');

        $result = $this->voter->vote($token, $topic, [TopicVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }
}
