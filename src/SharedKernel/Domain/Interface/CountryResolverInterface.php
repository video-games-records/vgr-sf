<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Interface;

use Symfony\Component\HttpFoundation\Request;

interface CountryResolverInterface
{
    /**
     * Returns the ISO 3166-1 alpha-2 country code for the request's visitor, or null if it cannot be resolved.
     */
    public function resolveCountry(Request $request): ?string;
}
