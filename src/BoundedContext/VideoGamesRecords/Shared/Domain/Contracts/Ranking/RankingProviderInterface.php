<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Domain\Contracts\Ranking;

interface RankingProviderInterface
{
    /**
     * @param array<string, mixed> $options
     * @return array<mixed>
     */
    public function getRankingPoints(?int $id = null, array $options = []): array;

    /**
     * @param array<string, mixed> $options
     * @return array<mixed>
     */
    public function getRankingMedals(?int $id = null, array $options = []): array;
}
