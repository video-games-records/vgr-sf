<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\PlayerChart;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlatformRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Core\Presentation\Form\SearchPlayerChartForm;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class Search extends AbstractLocalizedController
{
    private const SESSION_KEY_GAME_IDS = 'player_chart_search_game_ids';
    private const SESSION_KEY_GAME_DATA = 'player_chart_search_game_data';
    private const SESSION_KEY_PLAYER_IDS = 'player_chart_search_player_ids';
    private const SESSION_KEY_PLAYER_DATA = 'player_chart_search_player_data';
    private const SESSION_KEY_PLATFORM_IDS = 'player_chart_search_platform_ids';
    private const SESSION_KEY_PLATFORM_DATA = 'player_chart_search_platform_data';
    private const SESSION_KEY_STATUSES = 'player_chart_search_statuses';
    private const SESSION_KEY_RANK_OPERATOR = 'player_chart_search_rank_operator';
    private const SESSION_KEY_RANK_VALUE = 'player_chart_search_rank_value';
    private const SESSION_KEY_POINTS_OPERATOR = 'player_chart_search_points_operator';
    private const SESSION_KEY_POINTS_VALUE = 'player_chart_search_points_value';
    private const SESSION_KEY_PLATINUM_ONLY = 'player_chart_search_platinum_only';
    private const ITEMS_PER_PAGE = 20;

    public function __construct(
        private readonly PlayerChartRepository $playerChartRepository,
        private readonly GameRepository $gameRepository,
        private readonly PlayerRepository $playerRepository,
        private readonly PlatformRepository $platformRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/scores/search', name: 'vgr_score_search')]
    public function __invoke(Request $request): Response
    {
        $locale = $request->getLocale();
        $session = $request->getSession();
        $page = max(1, $request->query->getInt('page', 1));

        // Load statuses from session for form pre-fill
        $statusValues = $session->get(self::SESSION_KEY_STATUSES, []);
        /** @var PlayerChartStatusEnum[] $initialStatuses */
        $initialStatuses = array_filter(array_map(
            fn(string $value) => PlayerChartStatusEnum::tryFrom($value),
            $statusValues
        ));

        // Load rank filter from session
        $initialRankOperator = $session->get(self::SESSION_KEY_RANK_OPERATOR);
        $initialRankValue = $session->get(self::SESSION_KEY_RANK_VALUE);

        // Load points filter from session
        $initialPointsOperator = $session->get(self::SESSION_KEY_POINTS_OPERATOR);
        $initialPointsValue = $session->get(self::SESSION_KEY_POINTS_VALUE);

        // Load platinum filter from session
        $initialPlatinumOnly = $session->get(self::SESSION_KEY_PLATINUM_ONLY, false);

        $form = $this->createForm(SearchPlayerChartForm::class, [
            'statuses' => $initialStatuses,
            'rank_operator' => $initialRankOperator,
            'rank_value' => $initialRankValue,
            'points_operator' => $initialPointsOperator,
            'points_value' => $initialPointsValue,
            'platinum_only' => $initialPlatinumOnly,
        ], [
            'games_autocomplete_url' => $this->urlGenerator->generate('vgr_api_game_autocomplete'),
            'players_autocomplete_url' => $this->urlGenerator->generate('vgr_api_player_autocomplete'),
            'platforms_autocomplete_url' => $this->urlGenerator->generate('vgr_api_platform_autocomplete'),
            'locale' => $locale,
            'games_placeholder' => $locale === 'fr' ? 'Rechercher des jeux...' : 'Search games...',
            'players_placeholder' => $locale === 'fr' ? 'Rechercher des joueurs...' : 'Search players...',
            'platforms_placeholder' => $locale === 'fr' ? 'Rechercher des plateformes...' : 'Search platforms...',
        ]);

        $form->handleRequest($request);

        $result = null;
        $gameIds = [];
        $gamesData = [];
        $playerIds = [];
        $playersData = [];
        $platformIds = [];
        $platformsData = [];
        /** @var PlayerChartStatusEnum[] $statuses */
        $statuses = [];
        $rankOperator = null;
        $rankValue = null;
        $pointsOperator = null;
        $pointsValue = null;
        $platinumOnly = false;

        if ($form->isSubmitted() && $form->isValid()) {
            // New search: save to session and reset to page 1
            $data = $form->getData();

            // Handle games
            $gamesInput = $data['games'] ?? '';
            if (!empty($gamesInput)) {
                $gameIds = array_map('intval', array_filter(explode(',', $gamesInput)));
                $games = $this->gameRepository->findBy(['id' => $gameIds]);
                foreach ($games as $game) {
                    $gamesData[] = [
                        'id' => $game->getId(),
                        'text' => $game->getName($locale),
                    ];
                }
                $session->set(self::SESSION_KEY_GAME_IDS, $gameIds);
                $session->set(self::SESSION_KEY_GAME_DATA, $gamesData);
            } else {
                $session->remove(self::SESSION_KEY_GAME_IDS);
                $session->remove(self::SESSION_KEY_GAME_DATA);
            }

            // Handle players
            $playersInput = $data['players'] ?? '';
            if (!empty($playersInput)) {
                $playerIds = array_map('intval', array_filter(explode(',', $playersInput)));
                $players = $this->playerRepository->findBy(['id' => $playerIds]);
                foreach ($players as $player) {
                    $playersData[] = [
                        'id' => $player->getId(),
                        'text' => $player->getPseudo(),
                    ];
                }
                $session->set(self::SESSION_KEY_PLAYER_IDS, $playerIds);
                $session->set(self::SESSION_KEY_PLAYER_DATA, $playersData);
            } else {
                $session->remove(self::SESSION_KEY_PLAYER_IDS);
                $session->remove(self::SESSION_KEY_PLAYER_DATA);
            }

            // Handle platforms
            $platformsInput = $data['platforms'] ?? '';
            if (!empty($platformsInput)) {
                $platformIds = array_map('intval', array_filter(explode(',', $platformsInput)));
                $platforms = $this->platformRepository->findBy(['id' => $platformIds]);
                foreach ($platforms as $platform) {
                    $platformsData[] = [
                        'id' => $platform->getId(),
                        'text' => $platform->getName(),
                    ];
                }
                $session->set(self::SESSION_KEY_PLATFORM_IDS, $platformIds);
                $session->set(self::SESSION_KEY_PLATFORM_DATA, $platformsData);
            } else {
                $session->remove(self::SESSION_KEY_PLATFORM_IDS);
                $session->remove(self::SESSION_KEY_PLATFORM_DATA);
            }

            // Handle statuses
            $statuses = $data['statuses'] ?? [];
            if (!empty($statuses)) {
                $session->set(self::SESSION_KEY_STATUSES, array_map(fn(PlayerChartStatusEnum $s) => $s->value, $statuses));
            } else {
                $session->remove(self::SESSION_KEY_STATUSES);
            }

            // Handle rank filter
            $rankOperator = $data['rank_operator'] ?? null;
            $rankValue = $data['rank_value'] ?? null;
            if ($rankOperator !== null && $rankValue !== null) {
                $session->set(self::SESSION_KEY_RANK_OPERATOR, $rankOperator);
                $session->set(self::SESSION_KEY_RANK_VALUE, $rankValue);
            } else {
                $session->remove(self::SESSION_KEY_RANK_OPERATOR);
                $session->remove(self::SESSION_KEY_RANK_VALUE);
                $rankOperator = null;
                $rankValue = null;
            }

            // Handle points filter
            $pointsOperator = $data['points_operator'] ?? null;
            $pointsValue = $data['points_value'] ?? null;
            if ($pointsOperator !== null && $pointsValue !== null) {
                $session->set(self::SESSION_KEY_POINTS_OPERATOR, $pointsOperator);
                $session->set(self::SESSION_KEY_POINTS_VALUE, $pointsValue);
            } else {
                $session->remove(self::SESSION_KEY_POINTS_OPERATOR);
                $session->remove(self::SESSION_KEY_POINTS_VALUE);
                $pointsOperator = null;
                $pointsValue = null;
            }

            // Handle platinum filter
            $platinumOnly = $data['platinum_only'] ?? false;
            $session->set(self::SESSION_KEY_PLATINUM_ONLY, $platinumOnly);

            $page = 1;
        } else {
            // Pagination: load from session
            $gameIds = $session->get(self::SESSION_KEY_GAME_IDS, []);
            $gamesData = $session->get(self::SESSION_KEY_GAME_DATA, []);
            $playerIds = $session->get(self::SESSION_KEY_PLAYER_IDS, []);
            $playersData = $session->get(self::SESSION_KEY_PLAYER_DATA, []);
            $platformIds = $session->get(self::SESSION_KEY_PLATFORM_IDS, []);
            $platformsData = $session->get(self::SESSION_KEY_PLATFORM_DATA, []);
            $statuses = $initialStatuses;
            $rankOperator = $initialRankOperator;
            $rankValue = $initialRankValue;
            $pointsOperator = $initialPointsOperator;
            $pointsValue = $initialPointsValue;
            $platinumOnly = $initialPlatinumOnly;
        }

        $hasRankFilter = $rankOperator !== null && $rankValue !== null;
        $hasPointsFilter = $pointsOperator !== null && $pointsValue !== null;

        if (!empty($gameIds) || !empty($playerIds) || !empty($platformIds) || !empty($statuses) || $hasRankFilter || $hasPointsFilter || $platinumOnly) {
            $result = $this->playerChartRepository->search($gameIds, $playerIds, $platformIds, $statuses, $rankOperator, $rankValue, $pointsOperator, $pointsValue, $platinumOnly, $page, self::ITEMS_PER_PAGE);
        }

        return $this->render('@VideoGamesRecordsCore/player_chart/search.html.twig', [
            'form' => $form->createView(),
            'gamesData' => $gamesData,
            'playersData' => $playersData,
            'platformsData' => $platformsData,
            'statuses' => $statuses,
            'playerCharts' => $result ? $result['items'] : null,
            'pagination' => $result ? [
                'currentPage' => $page,
                'totalPages' => $result['pages'],
                'totalItems' => $result['total'],
            ] : null,
        ]);
    }
}
