<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Application\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Client\IgdbClient;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository\GenreRepository;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository\PlatformRepository;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository\PlatformTypeRepository;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository\PlatformLogoRepository;

class IgdbImportService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private IgdbClient $igdbClient,
        private GameRepository $gameRepository,
        private GenreRepository $genreRepository,
        private PlatformRepository $platformRepository,
        private PlatformTypeRepository $platformTypeRepository,
        private PlatformLogoRepository $platformLogoRepository
    ) {
    }

    /**
     * @return array{inserted: int, updated: int, skipped: int, total: int}
     */
    public function importGames(int $limit = 100, int $offset = 0): array
    {
        $games = $this->igdbClient->getAllGames($limit, $offset);

        $insertedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($games as $gameData) {
            $existingGame = $this->gameRepository->findByIgdbData($gameData);

            if ($existingGame) {
                $needsUpdate = $this->needsUpdate($existingGame->getUpdatedAt(), $gameData);

                if ($needsUpdate) {
                    $this->gameRepository->updateFromIgdbData($existingGame, $gameData);
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                $game = $this->gameRepository->createFromIgdbData($gameData);
                $this->entityManager->persist($game);
                $insertedCount++;
            }
        }

        $this->entityManager->flush();
        $total = $insertedCount + $updatedCount + $skippedCount;

        return [
            'inserted' => $insertedCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'total' => $total
        ];
    }

    /**
     * @return array{inserted: int, updated: int, skipped: int, total: int}
     */
    public function importGenres(int $limit = 50): array
    {
        $genres = $this->igdbClient->getAllGenres($limit);

        $insertedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($genres as $genreData) {
            $existingGenre = $this->genreRepository->findByIgdbData($genreData);

            if ($existingGenre) {
                $needsUpdate = $this->needsUpdate($existingGenre->getUpdatedAt(), $genreData);

                if ($needsUpdate) {
                    $this->genreRepository->updateFromIgdbData($existingGenre, $genreData);
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                $genre = $this->genreRepository->createFromIgdbData($genreData);
                $this->entityManager->persist($genre);
                $insertedCount++;
            }
        }

        $this->entityManager->flush();
        $total = $insertedCount + $updatedCount + $skippedCount;

        return [
            'inserted' => $insertedCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'total' => $total
        ];
    }

    /**
     * @return array{inserted: int, updated: int, skipped: int, total: int}
     */
    public function importPlatforms(int $limit = 100, int $offset = 0): array
    {
        $platforms = $this->igdbClient->getAllPlatforms($limit, $offset);

        $insertedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($platforms as $platformData) {
            $existingPlatform = $this->platformRepository->findByIgdbData($platformData);

            if ($existingPlatform) {
                $needsUpdate = $this->needsUpdate($existingPlatform->getUpdatedAt(), $platformData);

                if ($needsUpdate) {
                    $this->platformRepository->updateFromIgdbData($existingPlatform, $platformData);
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                $platform = $this->platformRepository->createFromIgdbData($platformData);
                $this->entityManager->persist($platform);
                $insertedCount++;
            }
        }

        $this->entityManager->flush();
        $total = $insertedCount + $updatedCount + $skippedCount;

        return [
            'inserted' => $insertedCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'total' => $total
        ];
    }

    /**
     * @return array{inserted: int, updated: int, skipped: int, total: int}
     */
    public function importPlatformTypes(int $limit = 50): array
    {
        $platformTypes = $this->igdbClient->getAllPlatformTypes($limit);

        $insertedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($platformTypes as $platformTypeData) {
            $existingPlatformType = $this->platformTypeRepository->findByIgdbData($platformTypeData);

            if ($existingPlatformType) {
                $needsUpdate = $this->needsUpdate($existingPlatformType->getUpdatedAt(), $platformTypeData);

                if ($needsUpdate) {
                    $this->platformTypeRepository->updateFromIgdbData($existingPlatformType, $platformTypeData);
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                $platformType = $this->platformTypeRepository->createFromIgdbData($platformTypeData);
                $this->entityManager->persist($platformType);
                $insertedCount++;
            }
        }

        $this->entityManager->flush();
        $total = $insertedCount + $updatedCount + $skippedCount;

        return [
            'inserted' => $insertedCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'total' => $total
        ];
    }

    /**
     * @return array{inserted: int, updated: int, skipped: int, total: int}
     */
    public function importPlatformLogos(int $limit = 100): array
    {
        $platformLogos = $this->igdbClient->getAllPlatformLogos($limit);

        $insertedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($platformLogos as $platformLogoData) {
            $existingPlatformLogo = $this->platformLogoRepository->findByIgdbData($platformLogoData);

            if ($existingPlatformLogo) {
                $needsUpdate = $this->needsUpdate($existingPlatformLogo->getUpdatedAt(), $platformLogoData);

                if ($needsUpdate) {
                    $this->platformLogoRepository->updateFromIgdbData($existingPlatformLogo, $platformLogoData);
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                $platformLogo = $this->platformLogoRepository->createFromIgdbData($platformLogoData);
                $this->entityManager->persist($platformLogo);
                $insertedCount++;
            }
        }

        $this->entityManager->flush();
        $total = $insertedCount + $updatedCount + $skippedCount;

        return [
            'inserted' => $insertedCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'total' => $total
        ];
    }

    /**
     * @param array<int>|null $platformIds
     * @return array{found: int, toImport: int, imported: int, skipped: int}
     */
    public function searchAndImportGames(
        string $gameName,
        ?array $platformIds = null,
        int $limit = 10,
        bool $exactMatch = false,
        bool $dryRun = false
    ): array {
        $games = $this->igdbClient->searchGamesByName($gameName, $platformIds, $limit);

        $foundCount = count($games);
        $toImportCount = 0;
        $importedCount = 0;
        $skippedCount = 0;

        $gamesToImport = [];

        foreach ($games as $gameData) {
            // Skip if exact match required and names don't match exactly
            if ($exactMatch && strcasecmp($gameData['name'], $gameName) !== 0) {
                continue;
            }

            // Check if game already exists in our database
            $existingGame = $this->gameRepository->findByIgdbData($gameData);
            if ($existingGame) {
                $skippedCount++;
                continue;
            }

            $gamesToImport[] = $gameData;
            $toImportCount++;
        }

        if (!$dryRun && !empty($gamesToImport)) {
            foreach ($gamesToImport as $gameData) {
                $game = $this->gameRepository->createFromIgdbData($gameData);
                $this->entityManager->persist($game);
                $importedCount++;
            }
            $this->entityManager->flush();
        }

        return [
            'found' => $foundCount,
            'toImport' => $toImportCount,
            'imported' => $importedCount,
            'skipped' => $skippedCount
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function needsUpdate(\DateTimeImmutable $existingUpdatedAt, array $data): bool
    {
        if (!isset($data['updated_at'])) {
            return true;
        }

        $igdbUpdatedAt = new \DateTimeImmutable('@' . $data['updated_at']);
        return $existingUpdatedAt < $igdbUpdatedAt;
    }
}
