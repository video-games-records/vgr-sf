<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Domain\Contracts\Ranking;

interface RankingCommandInterface
{
    public function handle(mixed $mixed): void;
}
