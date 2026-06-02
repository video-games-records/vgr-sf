<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Forum\Unit\Infrastructure\Security\Voter;

use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\Forum\Domain\ValueObject\ForumStatus;
use App\BoundedContext\Forum\Infrastructure\Security\Voter\ForumVoter;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[AllowMockObjectsWithoutExpectations]
class ForumVoterTest extends TestCase
{
    private ForumVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new ForumVoter();
    }

    private function makeToken(?UserInterface $user, string ...$roles): TokenInterface&MockObject
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $token->method('getRoleNames')->willReturn($roles);
        return $token;
    }

    private function makeUser(): UserInterface&MockObject
    {
        return $this->createMock(UserInterface::class);
    }

    private function makeForum(ForumStatus $status, ?string $role = null): Forum&MockObject
    {
        $forum = $this->createMock(Forum::class);
        $forum->method('getStatus')->willReturn($status);
        $forum->method('getRole')->willReturn($role);
        return $forum;
    }

    public function testSupportsForumViewOnForumSubject(): void
    {
        $forum = $this->makeForum(ForumStatus::PUBLIC);
        $token = $this->makeToken($this->makeUser());

        $result = $this->voter->vote($token, $forum, [ForumVoter::VIEW]);

        $this->assertNotSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testAbstainsOnWrongAttribute(): void
    {
        $forum = $this->createMock(Forum::class);
        $token = $this->makeToken(null);

        $result = $this->voter->vote($token, $forum, ['WRONG_ATTR']);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testAbstainsOnWrongSubject(): void
    {
        $token = $this->makeToken(null);

        $result = $this->voter->vote($token, new \stdClass(), [ForumVoter::VIEW]);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testPublicForumGrantsAccessToAnyone(): void
    {
        $forum = $this->makeForum(ForumStatus::PUBLIC);
        $token = $this->makeToken(null);

        $result = $this->voter->vote($token, $forum, [ForumVoter::VIEW]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testPrivateForumDeniesAnonymousUser(): void
    {
        $forum = $this->makeForum(ForumStatus::PRIVATE);
        $token = $this->makeToken(null);

        $result = $this->voter->vote($token, $forum, [ForumVoter::VIEW]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testPrivateForumWithoutRoleGrantsAuthenticatedUser(): void
    {
        $forum = $this->makeForum(ForumStatus::PRIVATE, null);
        $token = $this->makeToken($this->makeUser(), 'ROLE_USER');

        $result = $this->voter->vote($token, $forum, [ForumVoter::VIEW]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testPrivateForumWithRoleGrantsUserWithRole(): void
    {
        $forum = $this->makeForum(ForumStatus::PRIVATE, 'ROLE_MODERATOR');
        $token = $this->makeToken($this->makeUser(), 'ROLE_USER', 'ROLE_MODERATOR');

        $result = $this->voter->vote($token, $forum, [ForumVoter::VIEW]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testPrivateForumWithRoleDeniesUserWithoutRole(): void
    {
        $forum = $this->makeForum(ForumStatus::PRIVATE, 'ROLE_MODERATOR');
        $token = $this->makeToken($this->makeUser(), 'ROLE_USER');

        $result = $this->voter->vote($token, $forum, [ForumVoter::VIEW]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }
}
