<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Unit\Application\Service;

use App\BoundedContext\User\Application\Service\UserRegistrationService;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Domain\Event\UserRegisteredEvent;
use App\SharedKernel\Domain\Interface\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AllowMockObjectsWithoutExpectations]
class UserRegistrationServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private EventDispatcherInterface&MockObject $eventDispatcher;
    private UserRegistrationService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->service = new UserRegistrationService(
            $this->em,
            $this->passwordHasher,
            $this->eventDispatcher
        );
    }

    public function testRegisterUserThrowsWhenNoPlainPassword(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getPlainPassword')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->registerUser($user);
    }

    public function testRegisterUserHashesPasswordAndClearsPlainPassword(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getPlainPassword')->willReturn('secret');

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'secret')
            ->willReturn('hashed_secret');

        $user->expects($this->once())->method('setPassword')->with('hashed_secret');
        $user->expects($this->once())->method('setPlainPassword')->with(null);
        $user->method('setEnabled')->willReturnSelf();

        $this->em->expects($this->once())->method('persist')->with($user);
        $this->em->expects($this->once())->method('flush');

        $this->service->registerUser($user);
    }

    public function testRegisterUserAutoEnablesByDefault(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getPlainPassword')->willReturn('pass');
        $this->passwordHasher->method('hashPassword')->willReturn('hashed');
        $user->method('setPassword')->willReturnSelf();
        $user->method('setPlainPassword')->willReturnSelf();

        $user->expects($this->once())->method('setEnabled')->with(true);
        $user->expects($this->never())->method('setConfirmationToken');

        $this->service->registerUser($user, autoEnable: true);
    }

    public function testRegisterUserWithAutoEnableFalseSetsConfirmationToken(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getPlainPassword')->willReturn('pass');
        $this->passwordHasher->method('hashPassword')->willReturn('hashed');
        $user->method('setPassword')->willReturnSelf();
        $user->method('setPlainPassword')->willReturnSelf();

        $user->expects($this->once())->method('setEnabled')->with(false);
        $user->expects($this->once())->method('setConfirmationToken')->with($this->isType('string'));

        $this->service->registerUser($user, autoEnable: false);
    }

    public function testRegisterUserDispatchesUserRegisteredEvent(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getPlainPassword')->willReturn('pass');
        $this->passwordHasher->method('hashPassword')->willReturn('hashed');
        $user->method('setPassword')->willReturnSelf();
        $user->method('setPlainPassword')->willReturnSelf();
        $user->method('setEnabled')->willReturnSelf();

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UserRegisteredEvent::class));

        $this->service->registerUser($user);
    }

    public function testRegisterUserReturnsUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getPlainPassword')->willReturn('pass');
        $this->passwordHasher->method('hashPassword')->willReturn('hashed');
        $user->method('setPassword')->willReturnSelf();
        $user->method('setPlainPassword')->willReturnSelf();
        $user->method('setEnabled')->willReturnSelf();

        $result = $this->service->registerUser($user);

        $this->assertSame($user, $result);
    }

    public function testConfirmRegistrationEnablesUserAndClearsToken(): void
    {
        $user = $this->createMock(User::class);

        $user->expects($this->once())->method('setEnabled')->with(true);
        $user->expects($this->once())->method('setConfirmationToken')->with(null);

        $this->em->expects($this->once())->method('persist')->with($user);
        $this->em->expects($this->once())->method('flush');

        $this->service->confirmRegistration($user);
    }
}
