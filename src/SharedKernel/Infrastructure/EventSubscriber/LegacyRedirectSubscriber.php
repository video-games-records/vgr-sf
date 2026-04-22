<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\EventSubscriber;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\ChartRepository;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\ForumRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * Redirects old (legacy) URLs that return 404 to their new equivalents.
 *
 * Simple rules (no DB): add a pattern => route entry in RULES.
 *   Named regex captures become route parameters.
 *
 * DB-lookup rules: add a pattern in DB_RULES pointing to a resolve* method.
 */
final class LegacyRedirectSubscriber implements EventSubscriberInterface
{
    /**
     * Simple redirect rules (no DB lookup needed).
     *
     * Key   : regex pattern applied to the request path.
     * Value : Symfony route name (named captures → route params).
     *
     * @var array<string, string>
     */
    private const RULES = [
        // /{locale}/{game-slug}-game-g{id}/index
        // → vgr_game_show
        '~^/(?P<_locale>[a-z]{2})/(?P<slug>.+)-game-g(?P<id>\d+)/index/?$~' => 'vgr_game_show',

        // /{locale}/{game-slug}-game-g{id}/{group-slug}-group-g{groupId}/index
        // → vgr_group_show
        '~^/(?P<_locale>[a-z]{2})/(?P<slug>.+)-game-g(?P<id>\d+)/(?P<groupSlug>.+)-group-g(?P<groupId>\d+)/index/?$~' => 'vgr_group_show',

        // /{locale}/{game-slug}-game-g{id}/{group-slug}-group-g{groupId}/{chart-slug}-chart-c{chartId}/pc-{playerChartId}/index/
        // → vgr_player_chart_show
        '~^/(?P<_locale>[a-z]{2})/(?P<slug>.+)-game-g(?P<id>\d+)/(?P<groupSlug>.+)-group-g(?P<groupId>\d+)/(?P<chartSlug>.+)-chart-c(?P<chartId>\d+)/pc-(?P<playerChartId>\d+)/index/?$~' => 'vgr_player_chart_show',

        // /*.html/{game-slug}-game-g{id}/{group-slug}-group-g{groupId}/{chart-slug}-chart-c{chartId}/index
        // (lien relatif cliqué depuis une ancienne page .html, sans locale)
        // → vgr_chart_show
        '~^/[^/]*\.html/(?P<slug>[^/]+)-game-g(?P<id>\d+)/(?P<groupSlug>[^/]+)-group-g(?P<groupId>\d+)/(?P<chartSlug>[^/]+)-chart-c(?P<chartId>\d+)/index/?$~' => 'vgr_chart_show',

        // /{locale}/team/{id}-{slug}/{garbage}  (lien social/externe collé après l'URL équipe)
        // → vgr_team_profile_overview
        '~^/(?P<_locale>[a-z]{2})/team/(?P<id>\d+)-(?P<slug>[^/]+)/.+$~' => 'vgr_team_profile_overview',

        // /{locale}/{player-slug}-player-p{id}/profile
        // → vgr_player_profile_overview
        '~^/(?P<_locale>[a-z]{2}(-[a-z]{2})?)/(?P<slug>.+)-player-p(?P<id>\d+)/profile/?$~' => 'vgr_player_profile_overview',

        // /{locale}/{player-slug}-player-p{id}/presentation
        // → vgr_player_profile_overview
        '~^/(?P<_locale>[a-z]{2}(-[a-z]{2})?)/(?P<slug>.+)-player-p(?P<id>\d+)/presentation/?$~' => 'vgr_player_profile_overview',

        // /{locale}/{game-slug}-game-g{id}/{group-slug}-group-g{groupId}/{chart-slug}-chart-c{chartId}/index
        // → vgr_chart_show
        '~^/(?P<_locale>[a-z]{2})/(?P<slug>.+)-game-g(?P<id>\d+)/(?P<groupSlug>.+)-group-g(?P<groupId>\d+)/(?P<chartSlug>.+)-chart-c(?P<chartId>\d+)/index/?$~' => 'vgr_chart_show',
    ];

    /**
     * DB-lookup redirect rules.
     *
     * Key   : regex pattern applied to the request path.
     * Value : method name on this class to call with the regex matches.
     *
     * @var array<string, string>
     */
    private const DB_RULES = [
        // /{slug}-j{gameId}-m{playerId}-r{n}.html  (j = jeu)
        // → vgr_game_show (requires DB lookup for the game slug)
        '~^/.*-j(?P<gameId>\d+)-m\d+-r\d+\.html$~' => 'resolveGame',

        // /{slug}-jeu-j{gameId}.html
        // → vgr_game_show (requires DB lookup for the game slug)
        '~^/.*-jeu-j(?P<gameId>\d+)\.html/?$~' => 'resolveGame',

        // /{slug}-forum-f{forumId}-p{page}.html
        // → forum routes (requires DB lookup for the forum)
        '~^/.*-forum-f(?P<forumId>\d+)-p(?P<page>\d+)\.html/?$~' => 'resolveForum',

        // /{slug}-record-r{chartId}.html
        // → chart show (requires DB lookup for the chart)
        '~^/.*-record-r(?P<chartId>\d+)\.html/?$~' => 'resolveChart',

        // /game/{gameId}/picture
        // → https://picture.videogamesrecords.net/game/{game-slug}.jpg
        '~^/game/(?P<gameId>\d+)/picture/?$~' => 'resolveGamePicture',

        // /picture-p{pictureId}.html
        // → https://picture.videogamesrecords.net/proof/{pictureId}.jpg
        '~^/picture-p(?P<pictureId>\d+)\.html/?$~' => 'resolveProofPicture',
    ];

    public function __construct(
        private readonly RouterInterface $router,
        private readonly GameRepository $gameRepository,
        private readonly ForumRepository $forumRepository,
        private readonly ChartRepository $chartRepository,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->getThrowable() instanceof NotFoundHttpException) {
            return;
        }

        $path = $event->getRequest()->getPathInfo();

        foreach (self::RULES as $pattern => $route) {
            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            $params = array_filter(
                $matches,
                static fn (int|string $key) => is_string($key),
                ARRAY_FILTER_USE_KEY
            );

            $event->setResponse(new RedirectResponse($this->router->generate($route, $params), 301));

            return;
        }

        foreach (self::DB_RULES as $pattern => $method) {
            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            $url = $this->$method($matches);
            if ($url !== null) {
                $event->setResponse(new RedirectResponse($url, 301));
            }

            return;
        }
    }

    /**
     * Resolves a legacy record-check URL to vgr_game_show.
     *
     * Legacy pattern: /{slug}-j{gameId}-m{playerId}-r{n}.html  (j = jeu)
     *
     * @param array<int|string, string> $matches
     */
    private function resolveGame(array $matches): ?string
    {
        $game = $this->gameRepository->find((int) $matches['gameId']);

        if ($game === null) {
            return null;
        }

        return $this->router->generate('vgr_game_show', [
            '_locale' => 'en',
            'id'      => $game->getId(),
            'slug'    => $game->getSlug(),
        ]);
    }

    /**
     * Resolves a legacy forum URL to vgr_forum_show.
     *
     * Legacy pattern: /{slug}-forum-f{forumId}-p{page}.html
     *
     * @param array<int|string, string> $matches
     */
    private function resolveForum(array $matches): ?string
    {
        $forum = $this->forumRepository->find((int) $matches['forumId']);

        if ($forum === null) {
            return null;
        }

        return $this->router->generate('forum_show', [
            '_locale' => 'en',
            'id'      => $forum->getId(),
            'slug'    => $forum->getSlug(),
        ]);
    }

    /**
     * Resolves a legacy chart URL to vgr_chart_show.
     *
     * Legacy pattern: /{slug}-record-r{chartId}.html
     *
     * @param array<int|string, string> $matches
     */
    private function resolveChart(array $matches): ?string
    {
        $chart = $this->chartRepository->find((int) $matches['chartId']);

        if ($chart === null) {
            return null;
        }

        $group = $chart->getGroup();
        $game = $group->getGame();

        return $this->router->generate('vgr_chart_show', [
            '_locale' => 'en',
            'id' => $game->getId(),
            'slug' => $game->getSlug(),
            'groupId' => $group->getId(),
            'groupSlug' => $group->getSlug(),
            'chartId' => $chart->getId(),
            'chartSlug' => $chart->getSlug(),
        ]);
    }

    /**
     * Resolves a legacy game picture URL to the new picture domain.
     *
     * Legacy pattern: /game/{gameId}/picture
     *
     * @param array<int|string, string> $matches
     */
    private function resolveGamePicture(array $matches): ?string
    {
        $game = $this->gameRepository->find((int) $matches['gameId']);

        if ($game === null) {
            return null;
        }

        return 'https://picture.videogamesrecords.net/game/' . $game->getPicture();
    }

    /**
     * Resolves a legacy proof picture URL to the new picture domain.
     *
     * Legacy pattern: /picture-p{pictureId}.html
     *
     * @param array<int|string, string> $matches
     */
    private function resolveProofPicture(array $matches): ?string
    {
        return 'https://picture.videogamesrecords.net/proof/' . $matches['pictureId'] . '.jpg';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
