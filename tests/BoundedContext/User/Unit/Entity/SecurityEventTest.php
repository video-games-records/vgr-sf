<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Unit\Entity;

use App\BoundedContext\User\Domain\Entity\SecurityEvent;
use App\BoundedContext\User\Domain\Entity\User;
use App\SharedKernel\Domain\Security\SecurityEventTypeEnum;
use DateTime;
use PHPUnit\Framework\TestCase;

class SecurityEventTest extends TestCase
{
    private SecurityEvent $securityEvent;

    protected function setUp(): void
    {
        $this->securityEvent = new SecurityEvent();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorSetsCreatedAtToNow(): void
    {
        $before = new DateTime();
        $event = new SecurityEvent();
        $after = new DateTime();

        $this->assertGreaterThanOrEqual($before, $event->getCreatedAt());
        $this->assertLessThanOrEqual($after, $event->getCreatedAt());
    }

    // ------------------------------------------------------------------
    // Default values
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->securityEvent->getId());
    }

    public function testEventDataDefaultsToNull(): void
    {
        $this->assertNull($this->securityEvent->getEventData());
    }

    public function testIpAddressDefaultsToNull(): void
    {
        $this->assertNull($this->securityEvent->getIpAddress());
    }

    public function testUserAgentDefaultsToNull(): void
    {
        $this->assertNull($this->securityEvent->getUserAgent());
    }

    // ------------------------------------------------------------------
    // User relation
    // ------------------------------------------------------------------

    public function testSetAndGetUser(): void
    {
        $user = $this->createMock(User::class);
        $result = $this->securityEvent->setUser($user);

        $this->assertSame($user, $this->securityEvent->getUser());
        $this->assertSame($this->securityEvent, $result);
    }

    // ------------------------------------------------------------------
    // EventType
    // ------------------------------------------------------------------

    public function testSetAndGetEventType(): void
    {
        $result = $this->securityEvent->setEventType('login_success');

        $this->assertSame('login_success', $this->securityEvent->getEventType());
        $this->assertSame($this->securityEvent, $result);
    }

    public function testSetEventTypeFromEnum(): void
    {
        $result = $this->securityEvent->setEventTypeFromEnum(SecurityEventTypeEnum::LOGIN_SUCCESS);

        $this->assertSame('login_success', $this->securityEvent->getEventType());
        $this->assertSame($this->securityEvent, $result);
    }

    public function testGetEventTypeObject(): void
    {
        $this->securityEvent->setEventType(SecurityEventTypeEnum::LOGIN_FAILURE->value);

        $enum = $this->securityEvent->getEventTypeObject();

        $this->assertSame(SecurityEventTypeEnum::LOGIN_FAILURE, $enum);
    }

    public function testGetEventTypeObjectReturnsCorrectEnum(): void
    {
        $this->securityEvent->setEventTypeFromEnum(SecurityEventTypeEnum::REGISTRATION);

        $this->assertSame(SecurityEventTypeEnum::REGISTRATION, $this->securityEvent->getEventTypeObject());
    }

    // ------------------------------------------------------------------
    // CreatedAt
    // ------------------------------------------------------------------

    public function testSetAndGetCreatedAt(): void
    {
        $date = new DateTime('2025-06-15 12:00:00');
        $result = $this->securityEvent->setCreatedAt($date);

        $this->assertSame($date, $this->securityEvent->getCreatedAt());
        $this->assertSame($this->securityEvent, $result);
    }

    // ------------------------------------------------------------------
    // EventData
    // ------------------------------------------------------------------

    public function testSetAndGetEventData(): void
    {
        $data = ['ip' => '127.0.0.1', 'attempts' => 3];
        $result = $this->securityEvent->setEventData($data);

        $this->assertSame($data, $this->securityEvent->getEventData());
        $this->assertSame($this->securityEvent, $result);
    }

    public function testSetEventDataToNull(): void
    {
        $this->securityEvent->setEventData(['key' => 'value']);
        $result = $this->securityEvent->setEventData(null);

        $this->assertNull($this->securityEvent->getEventData());
        $this->assertSame($this->securityEvent, $result);
    }

    // ------------------------------------------------------------------
    // IpAddress
    // ------------------------------------------------------------------

    public function testSetAndGetIpAddress(): void
    {
        $result = $this->securityEvent->setIpAddress('192.168.1.1');

        $this->assertSame('192.168.1.1', $this->securityEvent->getIpAddress());
        $this->assertSame($this->securityEvent, $result);
    }

    public function testSetIpAddressToNull(): void
    {
        $this->securityEvent->setIpAddress('10.0.0.1');
        $result = $this->securityEvent->setIpAddress(null);

        $this->assertNull($this->securityEvent->getIpAddress());
        $this->assertSame($this->securityEvent, $result);
    }

    // ------------------------------------------------------------------
    // UserAgent
    // ------------------------------------------------------------------

    public function testSetAndGetUserAgent(): void
    {
        $ua = 'Mozilla/5.0 (X11; Linux x86_64)';
        $result = $this->securityEvent->setUserAgent($ua);

        $this->assertSame($ua, $this->securityEvent->getUserAgent());
        $this->assertSame($this->securityEvent, $result);
    }

    public function testSetUserAgentToNull(): void
    {
        $this->securityEvent->setUserAgent('SomeAgent/1.0');
        $result = $this->securityEvent->setUserAgent(null);

        $this->assertNull($this->securityEvent->getUserAgent());
        $this->assertSame($this->securityEvent, $result);
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getUserIdentifier')->willReturn('alice@example.com');

        $this->securityEvent->setUser($user);
        $this->securityEvent->setEventType('login_success');

        $this->assertSame('alice@example.com#login_success', (string) $this->securityEvent);
    }
}
