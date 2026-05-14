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

class SteamProvider implements PlatformProviderInterface
{
    private const string OPENID_ENDPOINT = 'https://steamcommunity.com/openid/login';
    private const string PROFILE_ENDPOINT = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/';
    private const string RECENT_GAMES_ENDPOINT = 'https://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v1/';
    private const string GAME_ICON_BASE = 'https://media.steampowered.com/steamcommunity/public/images/apps/';
    private const string STEAM_ID_PATTERN = '#^https://steamcommunity\.com/openid/id/(\d+)$#';
    private const string OPENID_NS = 'http://specs.openid.net/auth/2.0';
    private const string OPENID_IDENTIFIER = 'http://specs.openid.net/auth/2.0/identifier_select';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
    ) {
    }

    public function getAuthUrl(string $callbackUrl): string
    {
        $params = [
            'openid.ns' => self::OPENID_NS,
            'openid.mode' => 'checkid_setup',
            'openid.return_to' => $callbackUrl,
            'openid.realm' => $this->extractRealm($callbackUrl),
            'openid.identity' => self::OPENID_IDENTIFIER,
            'openid.claimed_id' => self::OPENID_IDENTIFIER,
        ];

        return self::OPENID_ENDPOINT . '?' . http_build_query($params);
    }

    public function handleCallback(Request $request): PlatformIdentity
    {
        // PHP converts dots to underscores in $_GET keys, so we parse the raw query string.
        $params = $this->parseRawQueryString($request->getQueryString() ?? '');

        if (($params['openid.mode'] ?? '') !== 'id_res') {
            throw new \RuntimeException('Steam authentication was cancelled or failed.');
        }

        $params['openid.mode'] = 'check_authentication';

        $response = $this->httpClient->request('POST', self::OPENID_ENDPOINT, [
            'body' => $params,
        ]);

        if (!str_contains($response->getContent(), 'is_valid:true')) {
            throw new \RuntimeException('Steam OpenID signature validation failed.');
        }

        $claimedId = $params['openid.claimed_id'] ?? '';
        preg_match(self::STEAM_ID_PATTERN, $claimedId, $matches);

        if (!isset($matches[1])) {
            throw new \RuntimeException('Unable to extract Steam ID from claimed_id.');
        }

        $steamId = $matches[1];
        $profile = $this->fetchProfile($steamId);

        return new PlatformIdentity(
            externalId: $steamId,
            username: $profile->username,
        );
    }

    public function fetchProfile(string $externalId): PlatformProfileData
    {
        $summaryResponse = $this->httpClient->request('GET', self::PROFILE_ENDPOINT, [
            'query' => ['key' => $this->apiKey, 'steamids' => $externalId],
        ]);
        $recentGamesResponse = $this->httpClient->request('GET', self::RECENT_GAMES_ENDPOINT, [
            'query' => ['key' => $this->apiKey, 'steamid' => $externalId, 'count' => 5],
        ]);

        $player = $summaryResponse->toArray()['response']['players'][0] ?? null;

        if ($player === null) {
            return new PlatformProfileData(
                externalId: $externalId,
                username: $externalId,
            );
        }

        $recentGames = [];
        foreach ($recentGamesResponse->toArray()['response']['games'] ?? [] as $game) {
            $iconHash = $game['img_icon_url'] ?? '';
            $recentGames[] = new RecentGame(
                appId: $game['appid'],
                name: $game['name'],
                playtimeForeverMinutes: $game['playtime_forever'] ?? 0,
                playtime2WeeksMinutes: $game['playtime_2weeks'] ?? 0,
                iconUrl: $iconHash !== '' ? self::GAME_ICON_BASE . $game['appid'] . '/' . $iconHash . '.jpg' : '',
            );
        }

        return new PlatformProfileData(
            externalId: $externalId,
            username: $player['personaname'],
            avatarUrl: $player['avatarmedium'] ?? null,
            onlineStatus: $player['personastate'] ?? 0,
            recentGames: $recentGames,
        );
    }

    public function supports(PlatformEnum $platform): bool
    {
        return $platform === PlatformEnum::STEAM;
    }

    /** @return array<string, string> */
    private function parseRawQueryString(string $queryString): array
    {
        $params = [];

        foreach (explode('&', $queryString) as $pair) {
            if (!str_contains($pair, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $pair, 2);
            $params[urldecode($key)] = urldecode($value);
        }

        return $params;
    }

    private function extractRealm(string $callbackUrl): string
    {
        $parts = parse_url($callbackUrl);

        return ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');
    }
}
