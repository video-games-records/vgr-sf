<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use DateTimeInterface;
use DateTimeImmutable;
use DateTimeZone;
use App\SharedKernel\Application\Service\TimezoneService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\BoundedContext\User\Domain\Entity\User;
use Symfony\Contracts\Translation\TranslatorInterface;

class TimezoneExtension extends AbstractExtension
{
    public function __construct(
        private readonly TimezoneService $timezoneService,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('user_date', [$this, 'formatUserDate']),
            new TwigFilter('user_datetime', [$this, 'formatUserDateTime']),
            new TwigFilter('user_time', [$this, 'formatUserTime']),
            new TwigFilter('user_timezone', [$this, 'convertToUserTimezone']),
            new TwigFilter('relative_time', [$this, 'formatRelativeTime']),
        ];
    }

    /**
     * Format a date in the user's timezone
     */
    public function formatUserDate(?DateTimeInterface $date, ?User $user = null, string $format = 'd/m/Y'): string
    {
        if (!$date) {
            return '';
        }

        $user = $user ?? $this->getCurrentUser();
        if (!$user) {
            // Fallback to UTC if no user
            $convertedDate = DateTimeImmutable::createFromInterface($date);
        } else {
            $convertedDate = $this->timezoneService->convertToUserTimezone($date, $user);
        }

        return $convertedDate->format($format);
    }

    /**
     * Format a datetime in the user's timezone
     */
    public function formatUserDateTime(?DateTimeInterface $date, ?User $user = null, string $format = 'd/m/Y H:i'): string
    {
        if (!$date) {
            return '';
        }

        $user = $user ?? $this->getCurrentUser();
        if (!$user) {
            // Fallback to UTC if no user
            $convertedDate = DateTimeImmutable::createFromInterface($date);
            return $convertedDate->format($format) . ' UTC';
        }

        $convertedDate = $this->timezoneService->convertToUserTimezone($date, $user);

        // Add timezone abbreviation
        $timezoneAbbr = $convertedDate->format('T');
        return $convertedDate->format($format) . ' ' . $timezoneAbbr;
    }

    /**
     * Format a time in the user's timezone
     */
    public function formatUserTime(?DateTimeInterface $date, ?User $user = null, string $format = 'H:i'): string
    {
        if (!$date) {
            return '';
        }

        $user = $user ?? $this->getCurrentUser();
        if (!$user) {
            // Fallback to UTC if no user
            $convertedDate = DateTimeImmutable::createFromInterface($date);
        } else {
            $convertedDate = $this->timezoneService->convertToUserTimezone($date, $user);
        }

        return $convertedDate->format($format);
    }

    /**
     * Convert a datetime to user's timezone (returns DateTimeInterface)
     */
    public function convertToUserTimezone(?DateTimeInterface $date, ?User $user = null): ?DateTimeInterface
    {
        if (!$date) {
            return null;
        }

        $user = $user ?? $this->getCurrentUser();
        if (!$user) {
            return $date;
        }

        return $this->timezoneService->convertToUserTimezone($date, $user);
    }

    /**
     * Format relative time (e.g., "2 hours ago", "in 3 days")
     */
    public function formatRelativeTime(?DateTimeInterface $date, ?User $user = null): string
    {
        if (!$date) {
            return '';
        }

        $user = $user ?? $this->getCurrentUser();
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        if ($user) {
            $date = $this->timezoneService->convertToUserTimezone($date, $user);
            $now = $this->timezoneService->convertToUserTimezone($now, $user);
        } else {
            $date = DateTimeImmutable::createFromInterface($date);
        }

        $diff = $now->diff($date);
        $isPast = $date < $now;

        // Calculate the most significant time unit
        if ($diff->y > 0) {
            $count = $diff->y;
            $unit = $count === 1 ? 'year' : 'years';
        } elseif ($diff->m > 0) {
            $count = $diff->m;
            $unit = $count === 1 ? 'month' : 'months';
        } elseif ($diff->d > 0) {
            $count = $diff->d;
            $unit = $count === 1 ? 'day' : 'days';
        } elseif ($diff->h > 0) {
            $count = $diff->h;
            $unit = $count === 1 ? 'hour' : 'hours';
        } elseif ($diff->i > 0) {
            $count = $diff->i;
            $unit = $count === 1 ? 'minute' : 'minutes';
        } else {
            return $this->translator->trans('time.just_now', [], 'SharedKernel');
        }

        if ($isPast) {
            return $this->translator->trans('time.time_ago', ['%count%' => $count, '%unit%' => $this->translator->trans('time.units.' . $unit, [], 'SharedKernel')], 'SharedKernel');
        } else {
            return $this->translator->trans('time.time_in', ['%count%' => $count, '%unit%' => $this->translator->trans('time.units.' . $unit, [], 'SharedKernel')], 'SharedKernel');
        }
    }

    /**
     * Get the current logged-in user
     */
    private function getCurrentUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
