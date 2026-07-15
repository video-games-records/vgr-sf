<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Service;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Game as IgdbGame;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Client\IgdbClient;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository\GameRepository as IgdbGameRepository;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository\GenreRepository as IgdbGenreRepository;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository\PlatformRepository as IgdbPlatformRepository;
use Doctrine\ORM\EntityManagerInterface;

class GameMatchingService
{
    private const API_SEARCH_LIMIT = 10;

    /**
     * Standalone roman numerals mapped to digits ("Final Fantasy VII" vs "Final Fantasy 7").
     * "i", "v" and "x" are excluded: too ambiguous as words or titles (e.g. "Mega Man X").
     */
    private const ROMAN_NUMERALS = [
        'ii' => '2', 'iii' => '3', 'iv' => '4', 'vi' => '6', 'vii' => '7', 'viii' => '8', 'ix' => '9',
        'xi' => '11', 'xii' => '12', 'xiii' => '13', 'xiv' => '14', 'xv' => '15',
        'xvi' => '16', 'xvii' => '17', 'xviii' => '18', 'xix' => '19', 'xx' => '20',
    ];

    public function __construct(
        private readonly GameRepository $coreGameRepository,
        private readonly IgdbGameRepository $igdbGameRepository,
        private readonly IgdbPlatformRepository $igdbPlatformRepository,
        private readonly IgdbGenreRepository $igdbGenreRepository,
        private readonly IgdbClient $igdbClient,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Try to match a VGR game to an IGDB game by name + platform overlap.
     *
     * Matching logic:
     * - Looks up local IGDB games with the same English name (case-insensitive exact match)
     * - If VGR platforms have IGDB links, filters candidates by platform overlap
     * - When no local candidate exists, searches the IGDB API, keeps exact-name results
     *   (after normalization) and imports the game into the local igdb_game table
     * - Returns a match only when exactly one candidate remains
     *
     * When $dryRun is false and a match is found, the link is persisted immediately.
     */
    public function match(Game $vgrGame, bool $dryRun = false, bool $localOnly = false): MatchResult
    {
        $vgrGameId = $vgrGame->getId();
        if ($vgrGameId === null) {
            return new MatchResult(null, 0);
        }

        $name = $vgrGame->getLibGameEn();
        $igdbPlatformIds = $this->collectIgdbPlatformIds($vgrGame);

        $candidates = $this->igdbGameRepository->findCandidatesByName($name);

        if (!empty($igdbPlatformIds)) {
            $candidates = array_values(array_filter(
                $candidates,
                fn(IgdbGame $igdbGame) => $this->hasPlatformOverlap($igdbGame, $igdbPlatformIds)
            ));
        }

        if (empty($candidates) && !$localOnly) {
            return $this->matchFromApi($vgrGameId, $name, $igdbPlatformIds, $dryRun);
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
     * Search the IGDB API by name (fuzzy), keep only exact-name matches after
     * normalization, and import the matched game into the local igdb_game table.
     *
     * @param array<int> $igdbPlatformIds
     */
    private function matchFromApi(int $vgrGameId, string $name, array $igdbPlatformIds, bool $dryRun): MatchResult
    {
        // Fuzzy search without platform filter: filtering at this stage can drop
        // same-named per-platform entries that the search endpoint doesn't return.
        $results = $this->igdbClient->searchGamesByName($name, null, self::API_SEARCH_LIMIT);

        $normalizedName = $this->normalizeName($name);
        $exactMatches = array_values(array_filter(
            $results,
            fn(array $data) => isset($data['name'], $data['id'])
                && $this->normalizeName($data['name']) === $normalizedName
        ));

        if (empty($exactMatches)) {
            return new MatchResult(null, 0, true);
        }

        // IGDB splits some games into one entry per platform, and the search
        // endpoint returns only part of them — fetch the complete homonym set.
        $candidates = [];
        foreach (array_unique(array_column($exactMatches, 'name')) as $igdbName) {
            foreach ($this->igdbClient->getGamesByExactName($igdbName) as $data) {
                if (isset($data['id'], $data['name'])) {
                    $candidates[(int) $data['id']] = $data;
                }
            }
        }

        if (empty($candidates)) {
            $candidates = array_column($exactMatches, null, 'id');
        }

        if (!empty($igdbPlatformIds)) {
            $candidates = array_filter(
                $candidates,
                fn(array $data) => !empty(array_intersect(
                    $igdbPlatformIds,
                    array_map('intval', (array) ($data['platforms'] ?? []))
                ))
            );
        }

        if (count($candidates) !== 1) {
            return new MatchResult(null, count($candidates), true);
        }

        $igdbGame = $this->importIgdbGame(reset($candidates), $dryRun);

        if (!$dryRun) {
            $this->coreGameRepository->updateIgdbGame($vgrGameId, $igdbGame);
        }

        return new MatchResult($igdbGame, 1, true);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function importIgdbGame(array $data, bool $dryRun): IgdbGame
    {
        $existing = $this->igdbGameRepository->findByIgdbData($data);
        if ($existing !== null) {
            return $existing;
        }

        $igdbGame = $this->igdbGameRepository->createFromIgdbData($data);
        $this->attachRelations($igdbGame, $data);

        if (!$dryRun) {
            $this->entityManager->persist($igdbGame);
            $this->entityManager->flush();
        }

        return $igdbGame;
    }

    /**
     * Attach already-imported platforms and genres referenced by their IGDB ids.
     *
     * @param array<string, mixed> $data
     */
    private function attachRelations(IgdbGame $igdbGame, array $data): void
    {
        foreach ((array) ($data['platforms'] ?? []) as $platformId) {
            if (is_int($platformId)) {
                $platform = $this->igdbPlatformRepository->find($platformId);
                if ($platform !== null) {
                    $igdbGame->addPlatform($platform);
                }
            }
        }

        foreach ((array) ($data['genres'] ?? []) as $genreId) {
            if (is_int($genreId)) {
                $genre = $this->igdbGenreRepository->find($genreId);
                if ($genre !== null) {
                    $igdbGame->addGenre($genre);
                }
            }
        }
    }

    /**
     * Normalize a name for comparison: lowercase, accents stripped, roman numerals
     * converted to digits, and anything that is not a letter or digit removed, so
     * punctuation/spacing differences ("Soul Calibur II" vs "SoulCalibur II") don't
     * block a match. Safety against collisions comes from the single-candidate rule
     * + platform overlap.
     */
    private function normalizeName(string $name): string
    {
        $name = mb_strtolower($name);

        if (class_exists(\Normalizer::class)) {
            $name = (string) \Normalizer::normalize($name, \Normalizer::FORM_D);
            $name = (string) preg_replace('/\p{Mn}+/u', '', $name);
        }

        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_map(
            fn(string $token) => self::ROMAN_NUMERALS[$token] ?? $token,
            $tokens
        );

        return implode('', $tokens);
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
