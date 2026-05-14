<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Infrastructure\Provider;

use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Provider\PlatformProviderInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Repository\PlatformConnectionRepositoryInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformIdentity;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformProfileData;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\RecentGame;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class XboxProvider implements PlatformProviderInterface
{
    private const string MS_AUTH_URL = 'https://login.microsoftonline.com/consumers/oauth2/v2.0/authorize';
    private const string MS_TOKEN_URL = 'https://login.microsoftonline.com/consumers/oauth2/v2.0/token';
    private const string XBL_AUTH_URL = 'https://user.auth.xboxlive.com/user/authenticate';
    private const string XSTS_AUTH_URL = 'https://xsts.auth.xboxlive.com/xsts/authorize';
    private const string PROFILE_URL = 'https://profile.xboxlive.com/users/me/profile/settings';
    private const string TITLES_URL = 'https://achievements.xboxlive.com/users/xuid(%s)/history/titles?maxItems=25';
    private const int RECENT_GAMES_LIMIT = 5;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly PlatformConnectionRepositoryInterface $repository,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {
    }

    public function getAuthUrl(string $callbackUrl): string
    {
        return self::MS_AUTH_URL . '?' . http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $callbackUrl,
            'scope' => 'XboxLive.signin XboxLive.offline_access',
            'response_mode' => 'query',
        ]);
    }

    public function handleCallback(Request $request): PlatformIdentity
    {
        if ($request->query->has('error')) {
            throw new \RuntimeException(sprintf(
                'Xbox authentication failed: %s',
                $request->query->getString('error_description') ?: $request->query->getString('error'),
            ));
        }

        $code = $request->query->getString('code');
        if ($code === '') {
            throw new \RuntimeException('No authorization code received from Microsoft.');
        }

        $callbackUrl = $request->getUriForPath($request->getPathInfo());
        $msTokens = $this->exchangeCodeForTokens($code, $callbackUrl);

        ['xsts_token' => $xstsToken, 'uhs' => $uhs] = $this->getXstsToken($msTokens['access_token']);
        $profile = $this->fetchXboxProfile($xstsToken, $uhs);

        $tokenData = json_encode([
            'xuid' => $profile['xuid'],
            'refresh_token' => $msTokens['refresh_token'],
            'access_token' => $msTokens['access_token'],
            'expires_at' => time() + $msTokens['expires_in'],
        ], JSON_THROW_ON_ERROR);

        return new PlatformIdentity(
            externalId: $profile['gamertag'],
            username: $profile['gamertag'],
            tokenData: $tokenData,
        );
    }

    public function fetchProfile(string $externalId): PlatformProfileData
    {
        $connection = $this->repository->findByPlatformAndExternalId(PlatformEnum::XBOX, $externalId);

        if ($connection === null || $connection->getTokenData() === null) {
            return new PlatformProfileData(externalId: $externalId, username: $externalId);
        }

        /** @var array{xuid: string, refresh_token: string, access_token: string, expires_at: int} $tokenData */
        $tokenData = json_decode($connection->getTokenData(), true);

        if ($tokenData['expires_at'] <= time()) {
            $refreshed = $this->refreshAccessToken($tokenData['refresh_token']);
            $tokenData['access_token'] = $refreshed['access_token'];
            $tokenData['refresh_token'] = $refreshed['refresh_token'];
            $tokenData['expires_at'] = time() + $refreshed['expires_in'];
            $connection->setTokenData(json_encode($tokenData, JSON_THROW_ON_ERROR));
            $this->repository->save($connection);
        }

        ['xsts_token' => $xstsToken, 'uhs' => $uhs] = $this->getXstsToken($tokenData['access_token']);
        $profile = $this->fetchXboxProfile($xstsToken, $uhs);
        $recentGames = $this->fetchRecentGames($xstsToken, $uhs, $tokenData['xuid']);

        return new PlatformProfileData(
            externalId: $externalId,
            username: $profile['gamertag'],
            avatarUrl: $profile['gamerpic'] ?? null,
            recentGames: $recentGames,
        );
    }

    public function supports(PlatformEnum $platform): bool
    {
        return $platform === PlatformEnum::XBOX;
    }

    /** @return array{access_token: string, refresh_token: string, expires_in: int} */
    private function exchangeCodeForTokens(string $code, string $redirectUri): array
    {
        $response = $this->httpClient->request('POST', self::MS_TOKEN_URL, [
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ],
        ]);

        /** @var array{access_token: string, refresh_token: string, expires_in: int} */
        return $response->toArray();
    }

    /** @return array{access_token: string, refresh_token: string, expires_in: int} */
    private function refreshAccessToken(string $refreshToken): array
    {
        $response = $this->httpClient->request('POST', self::MS_TOKEN_URL, [
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
                'scope' => 'XboxLive.signin XboxLive.offline_access',
            ],
        ]);

        /** @var array{access_token: string, refresh_token: string, expires_in: int} */
        return $response->toArray();
    }

    /** @return array{xsts_token: string, uhs: string} */
    private function getXstsToken(string $msAccessToken): array
    {
        $xblResponse = $this->httpClient->request('POST', self::XBL_AUTH_URL, [
            'json' => [
                'Properties' => [
                    'AuthMethod' => 'RPS',
                    'SiteName' => 'user.auth.xboxlive.com',
                    'RpsTicket' => 'd=' . $msAccessToken,
                ],
                'RelyingParty' => 'http://auth.xboxlive.com',
                'TokenType' => 'JWT',
            ],
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        ]);

        $xblData = $xblResponse->toArray();
        $xblToken = $xblData['Token'];

        try {
            $xstsResponse = $this->httpClient->request('POST', self::XSTS_AUTH_URL, [
                'json' => [
                    'Properties' => [
                        'SandboxId' => 'RETAIL',
                        'UserTokens' => [$xblToken],
                    ],
                    'RelyingParty' => 'http://xboxlive.com',
                    'TokenType' => 'JWT',
                ],
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
            ]);

            $xstsData = $xstsResponse->toArray();
        } catch (HttpExceptionInterface $e) {
            $body = json_decode((string) $e->getResponse()->getContent(false), true);
            $xErr = $body['XErr'] ?? 0;

            if ($xErr === 2148916233) {
                throw new \RuntimeException('This Microsoft account does not have an Xbox profile. Create one at xbox.com.');
            }

            if ($xErr === 2148916238) {
                throw new \RuntimeException('This account is a child account and requires parental permission.');
            }

            throw new \RuntimeException('Xbox XSTS authentication failed.');
        }

        return [
            'xsts_token' => $xstsData['Token'],
            'uhs' => $xstsData['DisplayClaims']['xui'][0]['uhs'],
        ];
    }

    /** @return array{gamertag: string, gamerpic: string|null, xuid: string|null} */
    private function fetchXboxProfile(string $xstsToken, string $uhs): array
    {
        $response = $this->httpClient->request('GET', self::PROFILE_URL, [
            'query' => ['settings' => 'Gamertag,GameDisplayPicRaw'],
            'headers' => $this->buildXboxAuthHeaders($xstsToken, $uhs),
        ]);

        $data = $response->toArray();
        $profileUser = $data['profileUsers'][0] ?? [];

        $settings = [];
        foreach ($profileUser['settings'] ?? [] as $setting) {
            $settings[$setting['id']] = $setting['value'];
        }

        return [
            'gamertag' => $settings['Gamertag'] ?? 'Unknown',
            'gamerpic' => $settings['GameDisplayPicRaw'] ?? null,
            'xuid' => $profileUser['id'] ?? null,
        ];
    }

    /** @return list<RecentGame> */
    private function fetchRecentGames(string $xstsToken, string $uhs, string $xuid): array
    {
        try {
            $response = $this->httpClient->request('GET', sprintf(self::TITLES_URL, $xuid), [
                'headers' => $this->buildXboxAuthHeaders($xstsToken, $uhs),
            ]);

            $titles = array_filter(
                $response->toArray()['titles'] ?? [],
                static fn(array $t): bool => ($t['titleType'] ?? '') === 'Game',
            );

            usort($titles, static function (array $a, array $b): int {
                $dateA = $a['achievement']['lastTitleGameplayTime'] ?? $a['lastUnlockTime'] ?? '';
                $dateB = $b['achievement']['lastTitleGameplayTime'] ?? $b['lastUnlockTime'] ?? '';

                return strcmp($dateB, $dateA);
            });

            $recentGames = [];
            foreach (array_slice($titles, 0, self::RECENT_GAMES_LIMIT) as $title) {
                $recentGames[] = new RecentGame(
                    appId: 0,
                    name: $title['name'],
                    playtimeForeverMinutes: 0,
                    playtime2WeeksMinutes: 0,
                    iconUrl: $title['displayImage'] ?? '',
                );
            }

            return $recentGames;
        } catch (\Exception) {
            return [];
        }
    }

    /** @return array<string, string> */
    private function buildXboxAuthHeaders(string $xstsToken, string $uhs): array
    {
        return [
            'Authorization' => sprintf('XBL3.0 x=%s;%s', $uhs, $xstsToken),
            'x-xbl-contract-version' => '2',
            'Accept' => 'application/json',
            'Accept-Language' => 'en-US',
        ];
    }
}
