<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject;

readonly class PlatformIdentity
{
    public function __construct(
        public string $externalId,
        public ?string $username,
    ) {
    }
}
