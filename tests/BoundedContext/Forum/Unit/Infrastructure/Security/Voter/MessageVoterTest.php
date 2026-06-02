<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Forum\Unit\Infrastructure\Security\Voter;

use App\BoundedContext\Forum\Domain\Entity\Message;
use App\BoundedContext\Forum\Infrastructure\Security\Voter\MessageVoter;
use App\BoundedContext\User\Domain\Entity\User;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[AllowMockObjectsWithoutExpectations]
class MessageVoterTest extends TestCase
{
    private MessageVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new MessageVoter();
    }

    private function makeToken(?UserInterface $user): TokenInterface&MockObject
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        return $token;
    }

    private function makeMessage(int $userId): Message&MockObject
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($userId);

        $message = $this->createMock(Message::class);
        $message->method('getUser')->willReturn($user);
        return $message;
    }

    public function testAbstainsOnWrongAttribute(): void
    {
        $message = $this->createMock(Message::class);
        $token = $this->makeToken(null);

        $result = $this->voter->vote($token, $message, ['WRONG_ATTR']);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testAbstainsOnWrongSubject(): void
    {
        $token = $this->makeToken(null);

        $result = $this->voter->vote($token, new \stdClass(), [MessageVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testDeniesWhenTokenHasNoUser(): void
    {
        $message = $this->makeMessage(1);
        $token = $this->makeToken(null);

        $result = $this->voter->vote($token, $message, [MessageVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testDeniesWhenTokenUserIsNotUserEntityInstance(): void
    {
        $message = $this->makeMessage(1);
        // UserInterface mock that is NOT the User domain entity → voter must deny
        $otherUser = $this->createMock(UserInterface::class);
        $token = $this->makeToken($otherUser);

        $result = $this->voter->vote($token, $message, [MessageVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testGrantsWhenUserOwnsMessage(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(42);

        $message = $this->makeMessage(42);
        $token = $this->makeToken($user);

        $result = $this->voter->vote($token, $message, [MessageVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testDeniesWhenUserDoesNotOwnMessage(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(99);

        $message = $this->makeMessage(42);
        $token = $this->makeToken($user);

        $result = $this->voter->vote($token, $message, [MessageVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }
}
