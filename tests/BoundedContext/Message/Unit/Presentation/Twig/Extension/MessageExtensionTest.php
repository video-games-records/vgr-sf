<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Message\Unit\Presentation\Twig\Extension;

use App\BoundedContext\Message\Domain\Repository\MessageRepositoryInterface;
use App\BoundedContext\Message\Presentation\Twig\Extension\MessageExtension;
use App\BoundedContext\User\Domain\Entity\User;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\TwigFunction;

#[AllowMockObjectsWithoutExpectations]
class MessageExtensionTest extends TestCase
{
    private MessageRepositoryInterface&MockObject $repository;
    private Security&Stub $security;
    private MessageExtension $extension;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MessageRepositoryInterface::class);
        $this->security = $this->createStub(Security::class);
        $this->extension = new MessageExtension($this->repository, $this->security);
    }

    // ------------------------------------------------------------------
    // getFunctions
    // ------------------------------------------------------------------

    public function testGetFunctionsReturnsArray(): void
    {
        $functions = $this->extension->getFunctions();
        $this->assertIsArray($functions);
    }

    public function testGetFunctionsContainsOneFunction(): void
    {
        $this->assertCount(1, $this->extension->getFunctions());
    }

    public function testGetFunctionsContainsTwigFunction(): void
    {
        $functions = $this->extension->getFunctions();
        $this->assertInstanceOf(TwigFunction::class, $functions[0]);
    }

    public function testGetFunctionNameIsGetUnreadMessagesCount(): void
    {
        $functions = $this->extension->getFunctions();
        $this->assertSame('get_unread_messages_count', $functions[0]->getName());
    }

    // ------------------------------------------------------------------
    // getUnreadMessagesCount — authenticated user
    // ------------------------------------------------------------------

    public function testGetUnreadMessagesCountReturnsRepositoryValueForAuthenticatedUser(): void
    {
        $user = $this->createMock(User::class);
        $this->security->method('getUser')->willReturn($user);

        $this->repository->expects($this->once())
            ->method('getNbNewMessage')
            ->with($user)
            ->willReturn(5);

        $this->assertSame(5, $this->extension->getUnreadMessagesCount());
    }

    public function testGetUnreadMessagesCountReturnsZeroWhenNoUnread(): void
    {
        $user = $this->createMock(User::class);
        $this->security->method('getUser')->willReturn($user);

        $this->repository->method('getNbNewMessage')->willReturn(0);

        $this->assertSame(0, $this->extension->getUnreadMessagesCount());
    }

    public function testGetUnreadMessagesCountReturnsLargeNumber(): void
    {
        $user = $this->createMock(User::class);
        $this->security->method('getUser')->willReturn($user);

        $this->repository->method('getNbNewMessage')->willReturn(999);

        $this->assertSame(999, $this->extension->getUnreadMessagesCount());
    }

    // ------------------------------------------------------------------
    // getUnreadMessagesCount — unauthenticated user
    // ------------------------------------------------------------------

    public function testGetUnreadMessagesCountReturnsZeroWhenUserIsNull(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $this->repository->expects($this->never())->method('getNbNewMessage');

        $this->assertSame(0, $this->extension->getUnreadMessagesCount());
    }

    public function testGetUnreadMessagesCountReturnsZeroWhenUserIsNotUserInstance(): void
    {
        // Returns a Symfony UserInterface that is NOT the domain User entity
        $nonDomainUser = $this->createMock(\Symfony\Component\Security\Core\User\UserInterface::class);
        $this->security->method('getUser')->willReturn($nonDomainUser);

        $this->repository->expects($this->never())->method('getNbNewMessage');

        $this->assertSame(0, $this->extension->getUnreadMessagesCount());
    }
}
