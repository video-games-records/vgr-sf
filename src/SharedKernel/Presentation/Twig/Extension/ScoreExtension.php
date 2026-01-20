<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Twig\Extension;

use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\ScoreTools;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ScoreExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_score', [$this, 'formatScore']),
        ];
    }

    public function formatScore(string|int|null $value, string $mask): string
    {
        return ScoreTools::formatScore($value, $mask);
    }
}
