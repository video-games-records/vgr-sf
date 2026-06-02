<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Unit\Application\Service;

use App\BoundedContext\User\Application\Service\SecurityHistoryManager;
use App\BoundedContext\User\Domain\Entity\SecurityEvent;
use App\BoundedContext\User\Domain\Entity\User;
use App\SharedKernel\Domain\Security\SecurityEventTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[AllowMockObjectsWithoutExpectations]
class SecurityHistoryManagerTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private RequestStack&MockObject $requestStack;
    private SecurityHistoryManager $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->service = new SecurityHistoryManager($this->em, $this->requestStack);
    }

    public function testRecordEventPersistsAndFlushes(): void
    {
        $user = $this->createMock(User::class);
        $this->requestStack->method('getCurrentRequest')->willReturn(null);

        $this->em->expects($this->once())->method('persist')->with($this->isInstanceOf(SecurityEvent::class));
        $this->em->expects($this->once())->method('flush');

        $this->service->recordEvent($user, SecurityEventTypeEnum::PASSWORD_CHANGE);
    }

    public function testRecordEventReturnsSecurityEvent(): void
    {
        $user = $this->createMock(User::class);
        $this->requestStack->method('getCurrentRequest')->willReturn(null);

        $result = $this->service->recordEvent($user, SecurityEventTypeEnum::PASSWORD_CHANGE);

        $this->assertInstanceOf(SecurityEvent::class, $result);
    }

    public function testRecordEventWithNoRequestUsesUnknownValues(): void
    {
        $user = $this->createMock(User::class);
        $this->requestStack->method('getCurrentRequest')->willReturn(null);

        $event = $this->service->recordEvent($user, SecurityEventTypeEnum::PASSWORD_CHANGE);

        $this->assertSame('unknown', $event->getIpAddress());
        $this->assertSame('unknown', $event->getUserAgent());
    }

    public function testRecordEventWithRequestExtractsIpAndUserAgent(): void
    {
        $user = $this->createMock(User::class);

        $request = $this->createMock(Request::class);
        $request->method('getClientIp')->willReturn('192.168.1.1');
        $headers = $this->createMock(HeaderBag::class);
        $headers->method('get')->with('User-Agent')->willReturn('Mozilla/5.0');
        $request->headers = $headers;

        $this->requestStack->method('getCurrentRequest')->willReturn($request);

        $event = $this->service->recordEvent($user, SecurityEventTypeEnum::PASSWORD_CHANGE);

        $this->assertSame('192.168.1.1', $event->getIpAddress());
        $this->assertSame('Mozilla/5.0', $event->getUserAgent());
    }

    public function testRecordEventSetsUserAndEventType(): void
    {
        $user = $this->createMock(User::class);
        $this->requestStack->method('getCurrentRequest')->willReturn(null);

        $event = $this->service->recordEvent(
            $user,
            SecurityEventTypeEnum::PASSWORD_CHANGE,
            ['reason' => 'test']
        );

        $this->assertSame($user, $event->getUser());
        $this->assertSame(SecurityEventTypeEnum::PASSWORD_CHANGE->value, $event->getEventType());
        $this->assertSame(['reason' => 'test'], $event->getEventData());
    }
}
