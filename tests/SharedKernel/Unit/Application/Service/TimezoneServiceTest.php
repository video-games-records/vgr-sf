<?php

declare(strict_types=1);

namespace App\Tests\SharedKernel\Unit\Application\Service;

use App\BoundedContext\User\Domain\Entity\User;
use App\SharedKernel\Application\Service\TimezoneService;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class TimezoneServiceTest extends TestCase
{
    private TimezoneService $service;
    private User&MockObject $user;

    protected function setUp(): void
    {
        $this->service = new TimezoneService();
        $this->user = $this->createMock(User::class);
    }

    // ------------------------------------------------------------------
    // convertToUserTimezone
    // ------------------------------------------------------------------

    public function testConvertToUserTimezoneChangesTimezone(): void
    {
        $this->user->method('getTimezone')->willReturn('Europe/Paris');

        $utc = new DateTimeImmutable('2024-03-15 10:00:00', new DateTimeZone('UTC'));
        $result = $this->service->convertToUserTimezone($utc, $this->user);

        $this->assertSame('Europe/Paris', $result->getTimezone()->getName());
    }

    public function testConvertToUserTimezonePreservesPoint(): void
    {
        $this->user->method('getTimezone')->willReturn('America/New_York');

        // UTC 15:00 → New York 11:00 (UTC-4 in summer / UTC-5 in winter)
        $utc = new DateTimeImmutable('2024-06-15 15:00:00', new DateTimeZone('UTC'));
        $result = $this->service->convertToUserTimezone($utc, $this->user);

        // UTC-4 (EDT) → 11:00
        $this->assertSame('11', $result->format('H'));
    }

    public function testConvertToUserTimezoneFromNonUtcInput(): void
    {
        $this->user->method('getTimezone')->willReturn('Asia/Tokyo');

        // Paris time is UTC+1 in winter; we pass a Paris datetime
        $paris = new DateTimeImmutable('2024-01-15 12:00:00', new DateTimeZone('Europe/Paris'));
        $result = $this->service->convertToUserTimezone($paris, $this->user);

        // Paris (UTC+1) 12:00 → UTC 11:00 → Tokyo (UTC+9) 20:00
        $this->assertSame('20', $result->format('H'));
        $this->assertSame('Asia/Tokyo', $result->getTimezone()->getName());
    }

    public function testConvertToUserTimezoneReturnsImmutable(): void
    {
        $this->user->method('getTimezone')->willReturn('UTC');
        $utc = new DateTimeImmutable('2024-01-01 00:00:00', new DateTimeZone('UTC'));

        $result = $this->service->convertToUserTimezone($utc, $this->user);
        $this->assertInstanceOf(DateTimeImmutable::class, $result);
    }

    // ------------------------------------------------------------------
    // convertToUtc
    // ------------------------------------------------------------------

    public function testConvertToUtcChangesTimezoneToUtc(): void
    {
        $this->user->method('getTimezone')->willReturn('Europe/Paris');

        $local = new DateTimeImmutable('2024-06-15 13:00:00', new DateTimeZone('Europe/Paris'));
        $result = $this->service->convertToUtc($local, $this->user);

        $this->assertSame('UTC', $result->getTimezone()->getName());
    }

    public function testConvertToUtcCorrectlySubtractsOffset(): void
    {
        $this->user->method('getTimezone')->willReturn('America/New_York');

        // New York (UTC-5 in winter) 10:00 → UTC 15:00
        $local = new DateTimeImmutable('2024-01-15 10:00:00', new DateTimeZone('America/New_York'));
        $result = $this->service->convertToUtc($local, $this->user);

        $this->assertSame('15', $result->format('H'));
    }

    // ------------------------------------------------------------------
    // getCommonTimezones
    // ------------------------------------------------------------------

    public function testGetCommonTimezonesReturnsNonEmptyArray(): void
    {
        $timezones = $this->service->getCommonTimezones();
        $this->assertNotEmpty($timezones);
    }

    public function testGetCommonTimezonesHasExpectedRegions(): void
    {
        $timezones = $this->service->getCommonTimezones();
        $this->assertArrayHasKey('America', $timezones);
        $this->assertArrayHasKey('Europe', $timezones);
        $this->assertArrayHasKey('Asia', $timezones);
        $this->assertArrayHasKey('UTC', $timezones);
    }

    public function testGetCommonTimezonesContainsNonEmptyIdentifiers(): void
    {
        $timezones = $this->service->getCommonTimezones();
        foreach ($timezones as $zone) {
            foreach (array_keys($zone) as $identifier) {
                $this->assertNotEmpty($identifier);
            }
        }
    }

    // ------------------------------------------------------------------
    // getUserCurrentTime
    // ------------------------------------------------------------------

    public function testGetUserCurrentTimeReturnsImmutable(): void
    {
        $this->user->method('getTimezone')->willReturn('UTC');
        $result = $this->service->getUserCurrentTime($this->user);
        $this->assertInstanceOf(DateTimeImmutable::class, $result);
    }

    public function testGetUserCurrentTimeHasCorrectTimezone(): void
    {
        $this->user->method('getTimezone')->willReturn('Asia/Tokyo');
        $result = $this->service->getUserCurrentTime($this->user);
        $this->assertSame('Asia/Tokyo', $result->getTimezone()->getName());
    }
}
