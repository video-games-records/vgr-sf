<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Unit\Domain\Event;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Domain\Event\EmailChangedEvent;
use App\BoundedContext\User\Domain\Event\PasswordChangedEvent;
use App\BoundedContext\User\Domain\Event\UserRegisteredEvent;
use PHPUnit\Framework\TestCase;

class UserDomainEventsTest extends TestCase
{
    // ------------------------------------------------------------------
    // EmailChangedEvent
    // ------------------------------------------------------------------

    public function testEmailChangedEventHoldsUserAndEmails(): void
    {
        $user = $this->createStub(User::class);
        $event = new EmailChangedEvent($user, 'old@example.com', 'new@example.com');

        $this->assertSame($user, $event->getUser());
        $this->assertSame('old@example.com', $event->getOldEmail());
        $this->assertSame('new@example.com', $event->getNewEmail());
    }

    // ------------------------------------------------------------------
    // PasswordChangedEvent
    // ------------------------------------------------------------------

    public function testPasswordChangedEventHoldsUser(): void
    {
        $user = $this->createStub(User::class);
        $event = new PasswordChangedEvent($user);

        $this->assertSame($user, $event->getUser());
    }

    // ------------------------------------------------------------------
    // UserRegisteredEvent
    // ------------------------------------------------------------------

    public function testUserRegisteredEventHoldsUser(): void
    {
        $user = $this->createStub(User::class);
        $event = new UserRegisteredEvent($user);

        $this->assertSame($user, $event->getUser());
    }
}
