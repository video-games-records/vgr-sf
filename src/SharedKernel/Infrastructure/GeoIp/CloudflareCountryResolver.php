<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\GeoIp;

use App\SharedKernel\Domain\Interface\CountryResolverInterface;
use Symfony\Component\HttpFoundation\Request;

final class CloudflareCountryResolver implements CountryResolverInterface
{
    public function resolveCountry(Request $request): ?string
    {
        $country = $request->headers->get('CF-IPCountry');

        if (null === $country || '' === $country || 'XX' === $country || 'T1' === $country) {
            return null;
        }

        return strtoupper($country);
    }
}
