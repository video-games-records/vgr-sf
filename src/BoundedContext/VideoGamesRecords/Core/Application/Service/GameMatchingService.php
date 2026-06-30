<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Service;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Game as IgdbGame;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository\GameRepository as IgdbGameRepository;

class GameMatchingService
{
    public function __construct(
        private readonly GameRepository $coreGameRepository,
        private readonly IgdbGameRepository $igdbGameRepository,
    ) {
    }

    /**
     * Try to match a VGR game to an IGDB game by name + platform overlap.
     *
     * Matching logic:
     * - Finds IGDB games with the same English name (case-insensitive exact match)
     * - If VGR platforms have IGDB links, filters candidates by platform overlap
     * - Returns a match only when exactly one candidate remains
     *
     * When $dryRun is false and a match is found, the link is persisted immediately.
     */
    public function match(Game $vgrGame, bool $dryRun = false): MatchResult
    {
        $vgrGameId = $vgrGame->getId();
        if ($vgrGameId === null) {
            return new MatchResult(null, 0);
        }

        $candidates = $this->igdbGameRepository->findCandidatesByName($vgrGame->getLibGameEn());

        if (empty($candidates)) {
            return new MatchResult(null, 0);
        }

        $igdbPlatformIds = $this->collectIgdbPlatformIds($vgrGame);

        if (!empty($igdbPlatformIds)) {
            $candidates = array_values(array_filter(
                $candidates,
                fn(IgdbGame $igdbGame) => $this->hasPlatformOverlap($igdbGame, $igdbPlatformIds)
            ));
        }

        if (count($candidates) !== 1) {
            return new MatchResult(null, count($candidates));
        }

        $matched = $candidates[0];

        if (!$dryRun) {
            $this->coreGameRepository->updateIgdbGame($vgrGameId, $matched);
        }

        return new MatchResult($matched, 1);
    }

    /**
     * @return array<int>
     */
    private function collectIgdbPlatformIds(Game $vgrGame): array
    {
        $ids = [];
        foreach ($vgrGame->getPlatforms() as $platform) {
            if ($platform->getIgdbPlatform() !== null) {
                $ids[] = $platform->getIgdbPlatform()->getId();
            }
        }
        return $ids;
    }

    /**
     * @param array<int> $vgrIgdbPlatformIds
     */
    private function hasPlatformOverlap(IgdbGame $igdbGame, array $vgrIgdbPlatformIds): bool
    {
        $igdbPlatformIds = $igdbGame->getPlatforms()
            ->map(fn($p) => $p->getId())
            ->toArray();

        return !empty(array_intersect($vgrIgdbPlatformIds, $igdbPlatformIds));
    }
}
