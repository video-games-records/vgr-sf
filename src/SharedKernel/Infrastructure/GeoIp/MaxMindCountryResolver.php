<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\GeoIp;

use App\SharedKernel\Domain\Interface\CountryResolverInterface;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class MaxMindCountryResolver implements CountryResolverInterface
{
    public function __construct(
        #[Autowire('%app.geoip.database_path%')]
        private readonly string $databasePath,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function resolveCountry(string $ip): ?string
    {
        if (!is_file($this->databasePath)) {
            $this->logger->warning('GeoIP database not found, skipping country resolution', [
                'path' => $this->databasePath,
            ]);

            return null;
        }

        try {
            $reader = new Reader($this->databasePath);

            return $reader->country($ip)->country->isoCode;
        } catch (AddressNotFoundException) {
            return null;
        } catch (InvalidDatabaseException | \InvalidArgumentException $e) {
            $this->logger->warning('GeoIP lookup failed', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
