<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Interface;

interface CountryResolverInterface
{
    /**
     * Returns the ISO 3166-1 alpha-2 country code for the given IP, or null if it cannot be resolved.
     */
    public function resolveCountry(string $ip): ?string;
}
