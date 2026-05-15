<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Infrastructure\Provider;

use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Provider\PlatformProviderInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformIdentity;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformProfileData;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\RecentGame;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RetroAchievementsProvider implements PlatformProviderInterface
{
    private const string MEDIA_BASE_URL = 'https://media.retroachievements.org';
    private const string PROFILE_URL = 'https://retroachievements.org/API/API_GetUserProfile.php';
    private const string RECENT_GAMES_URL = 'https://retroachievements.org/API/API_GetUserRecentlyPlayedGames.php';
    private const int RECENT_GAMES_LIMIT = 5;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
    ) {
    }

    public function getAuthUrl(string $callbackUrl): string
    {
        throw new \LogicException('RetroAchievements does not use OAuth — use RetroAchievementsLinkController instead.');
    }

    public function handleCallback(Request $request): PlatformIdentity
    {
        throw new \LogicException('RetroAchievements does not use OAuth — use RetroAchievementsLinkController instead.');
    }

    public function fetchProfile(string $externalId): PlatformProfileData
    {
        $profileResponse = $this->httpClient->request('GET', self::PROFILE_URL, [
            'query' => ['u' => $externalId, 'y' => $this->apiKey],
        ]);

        $profile = $profileResponse->toArray();

        if (!isset($profile['User'])) {
            throw new \RuntimeException(sprintf('RetroAchievements user "%s" not found.', $externalId));
        }

        $recentGames = $this->fetchRecentGames($externalId);
        $isOnline = isset($profile['Status']) && strtolower($profile['Status']) === 'online';

        return new PlatformProfileData(
            externalId: $externalId,
            username: $profile['User'],
            avatarUrl: self::MEDIA_BASE_URL . ($profile['UserPic'] ?? '/UserPic/' . $externalId . '.png'),
            onlineStatus: $isOnline ? 1 : 0,
            recentGames: $recentGames,
        );
    }

    public function supports(PlatformEnum $platform): bool
    {
        return $platform === PlatformEnum::RETRO_ACHIEVEMENTS;
    }

    /** @return list<RecentGame> */
    private function fetchRecentGames(string $username): array
    {
        try {
            $response = $this->httpClient->request('GET', self::RECENT_GAMES_URL, [
                'query' => ['u' => $username, 'c' => self::RECENT_GAMES_LIMIT, 'y' => $this->apiKey],
            ]);

            $recentGames = [];
            foreach ($response->toArray() as $game) {
                $recentGames[] = new RecentGame(
                    appId: (int) ($game['GameID'] ?? 0),
                    name: $game['Title'] ?? '',
                    playtimeForeverMinutes: 0,
                    playtime2WeeksMinutes: 0,
                    iconUrl: isset($game['ImageIcon']) ? self::MEDIA_BASE_URL . $game['ImageIcon'] : '',
                );
            }

            return $recentGames;
        } catch (\Exception) {
            return [];
        }
    }
}
