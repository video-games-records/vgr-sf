<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Client;

use GuzzleHttp\Client;
use KrisKuiper\IGDBV4\Authentication\Authentication;
use KrisKuiper\IGDBV4\IGDB;
use KrisKuiper\IGDBV4\Authentication\ValueObjects\AccessConfig;
use KrisKuiper\IGDBV4\Authentication\ValueObjects\AuthConfig;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Client\Endpoint\PlatformTypeEndpoint;

class IgdbClient
{
    private IGDB $api;
    private Client $client;
    private AccessConfig $accessConfig;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret
    ) {
        $this->client = new Client();
        $authConfig = new AuthConfig($this->clientId, $this->clientSecret);
        $authentication = new Authentication($this->client, $authConfig);
        $token = $authentication->obtainToken();

        $this->accessConfig = new AccessConfig($this->clientId, $token->getAccessToken());
        $this->api = new IGDB($this->client, $this->accessConfig);
    }

    /**
     * @return array<mixed>
     */
    public function getAllGenres(int $limit = 500): array
    {
        $genres = [];
        $collection = $this->api->genre()->list(0, $limit, ['id', 'name', 'slug', 'url', 'created_at', 'updated_at']);

        foreach ($collection as $genre) {
            $genres[] = (array) $genre;
        }

        return $genres;
    }

    /**
     * @return array<mixed>
     */
    public function getAllPlatformTypes(int $limit = 500): array
    {
        $platformTypes = [];
        $platformTypeEndpoint = new PlatformTypeEndpoint($this->client, $this->accessConfig);
        $collection = $platformTypeEndpoint->list(
            0,
            $limit,
            ['id', 'name', 'checksum', 'created_at', 'updated_at']
        );

        foreach ($collection as $platformType) {
            $platformTypes[] = (array) $platformType;
        }

        return $platformTypes;
    }

    /**
     * @return array<mixed>
     */
    public function getAllPlatforms(int $limit = 500, int $offset = 0): array
    {
        $platforms = [];
        $collection = $this->api->platform()->list(
            $offset,
            $limit,
            [
                'id', 'name', 'abbreviation', 'alternative_name', 'generation',
                'slug', 'summary', 'url', 'checksum', 'platform_type',
                'platform_logo', 'created_at', 'updated_at'
            ]
        );

        foreach ($collection as $platform) {
            $platforms[] = (array) $platform;
        }

        return $platforms;
    }

    /**
     * @return array<mixed>
     */
    public function getAllPlatformLogos(int $limit = 500): array
    {
        $platformLogos = [];
        $collection = $this->api->platformLogo()->list(
            0,
            $limit,
            [
                'id', 'alpha_channel', 'animated', 'checksum', 'height',
                'image_id', 'url', 'width', 'created_at', 'updated_at'
            ]
        );

        foreach ($collection as $platformLogo) {
            $platformLogos[] = (array) $platformLogo;
        }

        return $platformLogos;
    }

    /**
     * @return array<mixed>
     */
    public function getAllGames(int $limit = 500, int $offset = 0): array
    {
        $games = [];
        $collection = $this->api->game()->list(
            $offset,
            $limit,
            [
                'id', 'name', 'slug', 'storyline', 'summary', 'url', 'checksum',
                'first_release_date', 'version_parent', 'genres', 'platforms',
                'created_at', 'updated_at'
            ]
        );

        foreach ($collection as $game) {
            $games[] = (array) $game;
        }

        return $games;
    }

    /**
     * @param array<int>|null $platformIds
     * @return array<mixed>
     */
    public function searchGamesByName(string $gameName, ?array $platformIds = null, int $limit = 10): array
    {
        $games = [];

        // Use the search method which returns results directly
        $searchResults = $this->api->game()->search($gameName);

        $count = 0;
        foreach ($searchResults as $game) {
            if ($count >= $limit) {
                break;
            }

            $gameData = (array) $game;

            // Filter by platforms if specified
            if (!empty($platformIds) && isset($gameData['platforms'])) {
                $gamePlatforms = is_array($gameData['platforms']) ? $gameData['platforms'] : [$gameData['platforms']];
                $hasMatchingPlatform = false;

                foreach ($gamePlatforms as $platform) {
                    $platformId = is_array($platform) ? ($platform['id'] ?? $platform) : $platform;
                    if (in_array($platformId, $platformIds, true)) {
                        $hasMatchingPlatform = true;
                        break;
                    }
                }

                if (!$hasMatchingPlatform) {
                    continue;
                }
            }

            $games[] = $gameData;
            $count++;
        }

        return $games;
    }

    /**
     * @param array<string> $gameNames
     * @return array<mixed>
     */
    public function searchGames(array $gameNames): array
    {
        $games = [];

        foreach ($gameNames as $gameName) {
            $searchResults = $this->api->game()->search($gameName);

            foreach ($searchResults as $game) {
                $gameData = (array) $game;
                // Éviter les doublons
                if (!isset($games[$gameData['id']])) {
                    $games[$gameData['id']] = $gameData;
                }
            }
        }

        return array_values($games);
    }

    /**
     * @param array<int> $gameIds
     * @return array<mixed>
     */
    public function getGamesByIds(array $gameIds): array
    {
        if (empty($gameIds)) {
            return [];
        }

        $games = [];

        // Get games in batches since we can't use whereIn
        foreach (array_chunk($gameIds, 10) as $batch) {
            foreach ($batch as $gameId) {
                try {
                    $collection = $this->api->game()->list(
                        0,
                        1,
                        [
                            'id', 'name', 'slug', 'storyline', 'summary', 'url', 'checksum',
                            'first_release_date', 'version_parent', 'genres', 'platforms',
                            'created_at', 'updated_at'
                        ]
                    );

                    foreach ($collection as $game) {
                        $games[] = (array) $game;
                    }
                } catch (\Exception $e) {
                    // Skip games that can't be found
                    continue;
                }
            }
        }

        return $games;
    }
}
