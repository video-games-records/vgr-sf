<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\EventSubscriber;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
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
    ];

    public function __construct(
        private readonly RouterInterface $router,
        private readonly GameRepository $gameRepository,
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

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
