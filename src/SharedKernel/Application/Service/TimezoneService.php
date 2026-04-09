<?php

declare(strict_types=1);

namespace App\SharedKernel\Application\Service;

use DateTimeInterface;
use DateTimeImmutable;
use DateTimeZone;
use App\BoundedContext\User\Domain\Entity\User;

class TimezoneService
{
    /**
     * Convert a UTC datetime to user's timezone
     */
    public function convertToUserTimezone(DateTimeInterface $dateTime, User $user): DateTimeImmutable
    {
        $immutableDateTime = DateTimeImmutable::createFromInterface($dateTime);

        // Ensure the datetime is in UTC
        if ($immutableDateTime->getTimezone()->getName() !== 'UTC') {
            $immutableDateTime = $immutableDateTime->setTimezone(new DateTimeZone('UTC'));
        }

        // Convert to user's timezone
        $userTimezone = new DateTimeZone($user->getTimezone());
        return $immutableDateTime->setTimezone($userTimezone);
    }

    /**
     * Convert a user's local time to UTC
     */
    public function convertToUtc(DateTimeInterface $dateTime, User $user): DateTimeImmutable
    {
        $immutableDateTime = DateTimeImmutable::createFromInterface($dateTime);

        // Set to user's timezone if not already
        if ($immutableDateTime->getTimezone()->getName() !== $user->getTimezone()) {
            $userTimezone = new DateTimeZone($user->getTimezone());
            $immutableDateTime = $immutableDateTime->setTimezone($userTimezone);
        }

        // Convert to UTC
        return $immutableDateTime->setTimezone(new DateTimeZone('UTC'));
    }

    /**
     * Get list of common timezones grouped by region
     *
     * @return array<string, array<string, string>>
     */
    public function getCommonTimezones(): array
    {
        return [
            'America' => [
                'America/New_York' => 'Eastern Time (US & Canada)',
                'America/Chicago' => 'Central Time (US & Canada)',
                'America/Denver' => 'Mountain Time (US & Canada)',
                'America/Los_Angeles' => 'Pacific Time (US & Canada)',
                'America/Toronto' => 'Toronto',
                'America/Mexico_City' => 'Mexico City',
                'America/Sao_Paulo' => 'São Paulo',
                'America/Buenos_Aires' => 'Buenos Aires',
            ],
            'Europe' => [
                'Europe/London' => 'London',
                'Europe/Paris' => 'Paris',
                'Europe/Berlin' => 'Berlin',
                'Europe/Madrid' => 'Madrid',
                'Europe/Rome' => 'Rome',
                'Europe/Amsterdam' => 'Amsterdam',
                'Europe/Brussels' => 'Brussels',
                'Europe/Stockholm' => 'Stockholm',
                'Europe/Moscow' => 'Moscow',
            ],
            'Asia' => [
                'Asia/Tokyo' => 'Tokyo',
                'Asia/Seoul' => 'Seoul',
                'Asia/Shanghai' => 'Beijing, Shanghai',
                'Asia/Hong_Kong' => 'Hong Kong',
                'Asia/Singapore' => 'Singapore',
                'Asia/Dubai' => 'Dubai',
                'Asia/Kolkata' => 'Mumbai, New Delhi',
                'Asia/Bangkok' => 'Bangkok',
            ],
            'Pacific' => [
                'Australia/Sydney' => 'Sydney',
                'Australia/Melbourne' => 'Melbourne',
                'Australia/Brisbane' => 'Brisbane',
                'Australia/Perth' => 'Perth',
                'Pacific/Auckland' => 'Auckland',
            ],
            'Africa' => [
                'Africa/Cairo' => 'Cairo',
                'Africa/Johannesburg' => 'Johannesburg',
                'Africa/Lagos' => 'Lagos',
                'Africa/Nairobi' => 'Nairobi',
            ],
            'UTC' => [
                'UTC' => 'UTC (Universal Coordinated Time)',
            ],
        ];
    }

    /**
     * Get current time in user's timezone
     */
    public function getUserCurrentTime(User $user): DateTimeImmutable
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        return $this->convertToUserTimezone($now, $user);
    }
}
