<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Player;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Player\PlayerDataProvider;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Country\CountryDTO;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/players/{id}',
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Player'],
                summary: 'Get player details',
                description: 'Retrieves detailed information about a specific player including stats, social links, and team information'
            ),
            provider: PlayerDataProvider::class
        ),
    ]
)]
class PlayerDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $pseudo,
        public readonly string $slug,
        public readonly int $nbConnexion,
        public readonly bool $hasDonate,
        public readonly PlayerStatsDTO $stats,
        public readonly ?\DateTimeInterface $lastLogin,
        public readonly ?\DateTimeInterface $createdAt,
        public readonly ?string $presentation,
        public readonly ?string $collection,
        #[ApiProperty(readableLink: true)]
        public readonly ?CountryDTO $country,
        public readonly ?\DateTimeInterface $birthDate,
    ) {
    }
}
