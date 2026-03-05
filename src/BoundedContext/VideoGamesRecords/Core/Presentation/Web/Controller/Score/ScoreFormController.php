<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Score;

use App\BoundedContext\VideoGamesRecords\Core\Application\Service\PlayerScoreFormService;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\ChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GroupRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
#[IsGranted('ROLE_PLAYER')]
class ScoreFormController extends AbstractLocalizedController
{
    public function __construct(
        private readonly ChartRepository $chartRepository,
        private readonly GameRepository $gameRepository,
        private readonly GroupRepository $groupRepository,
        private readonly UserProvider $userProvider,
        private readonly PlayerScoreFormService $playerScoreFormService,
    ) {
    }

    #[Route('/game/{id}-{slug}/scores', name: 'vgr_game_scores', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function gameScores(Request $request, int $id, string $slug, string $_locale): Response
    {
        $game = $this->gameRepository->find($id);

        if (!$game || $game->getSlug() !== $slug) {
            throw $this->createNotFoundException('Game not found');
        }

        $player = $this->userProvider->getPlayer();
        if (!$player) {
            throw $this->createAccessDeniedException('Player not found');
        }

        // Handle form submission
        if ($request->isMethod('POST')) {
            return $this->handleSubmission($request, $player, $this->generateUrl('vgr_game_scores', [
                'id' => $id,
                'slug' => $slug,
                '_locale' => $_locale,
                'page' => $request->query->getInt('page', 1),
                'search' => $request->query->get('search', ''),
            ]));
        }

        $page = $request->query->getInt('page', 1);
        $searchTerm = $request->query->get('search', '');

        $search = ['game' => $game];
        if ($searchTerm !== '') {
            $search['term'] = $searchTerm;
        }

        $paginator = $this->chartRepository->getList($player, $page, $search, $_locale, 20);

        // Prepare chart data for template
        $chartsData = $this->prepareChartsData($paginator, $player);

        return $this->render('@VideoGamesRecordsCore/score/form.html.twig', [
            'game' => $game,
            'group' => null,
            'chart' => null,
            'chartsData' => $chartsData,
            'paginator' => $paginator,
            'page' => $page,
            'searchTerm' => $searchTerm,
            'context' => 'game',
            'platforms' => $game->getPlatforms(),
        ]);
    }

    #[Route('/game/{id}-{slug}/group/{groupId}-{groupSlug}/scores', name: 'vgr_group_scores', requirements: ['id' => '\d+', 'groupId' => '\d+'], methods: ['GET', 'POST'])]
    public function groupScores(Request $request, int $id, string $slug, int $groupId, string $groupSlug, string $_locale): Response
    {
        $group = $this->groupRepository->find($groupId);

        if (!$group || $group->getSlug() !== $groupSlug) {
            throw $this->createNotFoundException('Group not found');
        }

        if ($group->getGame()->getId() !== $id || $group->getGame()->getSlug() !== $slug) {
            throw $this->createNotFoundException('Group does not belong to this game');
        }

        $game = $group->getGame();
        $player = $this->userProvider->getPlayer();
        if (!$player) {
            throw $this->createAccessDeniedException('Player not found');
        }

        // Handle form submission
        if ($request->isMethod('POST')) {
            return $this->handleSubmission($request, $player, $this->generateUrl('vgr_group_scores', [
                'id' => $id,
                'slug' => $slug,
                'groupId' => $groupId,
                'groupSlug' => $groupSlug,
                '_locale' => $_locale,
                'page' => $request->query->getInt('page', 1),
            ]));
        }

        $page = $request->query->getInt('page', 1);

        $paginator = $this->chartRepository->getList($player, $page, ['group' => $group], $_locale, 20);

        // Prepare chart data for template
        $chartsData = $this->prepareChartsData($paginator, $player);

        return $this->render('@VideoGamesRecordsCore/score/form.html.twig', [
            'game' => $game,
            'group' => $group,
            'chart' => null,
            'chartsData' => $chartsData,
            'paginator' => $paginator,
            'page' => $page,
            'searchTerm' => '',
            'context' => 'group',
            'platforms' => $game->getPlatforms(),
        ]);
    }

    #[Route('/game/{id}-{slug}/group/{groupId}-{groupSlug}/chart/{chartId}-{chartSlug}/score', name: 'vgr_chart_score', requirements: ['id' => '\d+', 'groupId' => '\d+', 'chartId' => '\d+'], methods: ['GET', 'POST'])]
    public function chartScore(Request $request, int $id, string $slug, int $groupId, string $groupSlug, int $chartId, string $chartSlug, string $_locale): Response
    {
        $chart = $this->chartRepository->find($chartId);

        if (!$chart || $chart->getSlug() !== $chartSlug) {
            throw $this->createNotFoundException('Chart not found');
        }

        $group = $chart->getGroup();
        if ($group->getId() !== $groupId || $group->getSlug() !== $groupSlug) {
            throw $this->createNotFoundException('Chart does not belong to this group');
        }

        $game = $group->getGame();
        if ($game->getId() !== $id || $game->getSlug() !== $slug) {
            throw $this->createNotFoundException('Group does not belong to this game');
        }

        $player = $this->userProvider->getPlayer();
        if (!$player) {
            throw $this->createAccessDeniedException('Player not found');
        }

        // Handle form submission
        if ($request->isMethod('POST')) {
            return $this->handleSubmission($request, $player, $this->generateUrl('vgr_chart_score', [
                'id' => $id,
                'slug' => $slug,
                'groupId' => $groupId,
                'groupSlug' => $groupSlug,
                'chartId' => $chartId,
                'chartSlug' => $chartSlug,
                '_locale' => $_locale,
            ]));
        }

        $paginator = $this->chartRepository->getList($player, 1, ['chart' => $chart], $_locale, 1);

        // Prepare chart data for template
        $chartsData = $this->prepareChartsData($paginator, $player);

        return $this->render('@VideoGamesRecordsCore/score/form.html.twig', [
            'game' => $game,
            'group' => $group,
            'chart' => $chart,
            'chartsData' => $chartsData,
            'paginator' => $paginator,
            'page' => 1,
            'searchTerm' => '',
            'context' => 'chart',
            'platforms' => $game->getPlatforms(),
        ]);
    }

    /**
     * Handle form submission
     *
     * @param Request $request
     * @param \App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player $player
     * @param string $redirectUrl
     * @return Response
     */
    private function handleSubmission(Request $request, \App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player $player, string $redirectUrl): Response
    {
        if (!$this->isCsrfTokenValid('score_form', (string) $request->request->get('_token') ?: null)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $formData = $request->request->all('scores');

        if (empty($formData)) {
            $this->addFlash('warning', 'No scores to submit.');
            return $this->redirect($redirectUrl);
        }

        $result = $this->playerScoreFormService->processSubmission($player, $formData);

        if ($result['created'] > 0 || $result['updated'] > 0) {
            $message = '';
            if ($result['created'] > 0) {
                $message .= sprintf('%d score(s) created. ', $result['created']);
            }
            if ($result['updated'] > 0) {
                $message .= sprintf('%d score(s) updated.', $result['updated']);
            }
            $this->addFlash('success', trim($message));
        } else {
            $this->addFlash('info', 'No changes were made.');
        }

        return $this->redirect($redirectUrl);
    }

    /**
     * Prepare chart data with existing values for the template
     *
     * @param iterable<Chart> $charts
     * @param \App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player $player
     * @return array<array<string, mixed>>
     */
    private function prepareChartsData(iterable $charts, \App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player $player): array
    {
        $chartsData = [];

        foreach ($charts as $chart) {
            // Find player's chart if exists
            $playerChart = null;
            foreach ($chart->getPlayerCharts() as $pc) {
                if ($pc->getPlayer()->getId() === $player->getId()) {
                    $playerChart = $pc;
                    break;
                }
            }

            // Prepare existing values for each lib
            /** @var array<int|string, array<int|string, string>> $existingValues */
            $existingValues = [];
            /** @var array<string, mixed> $originalData */
            $originalData = [
                'platform' => $playerChart?->getPlatform()?->getId(),
                'hasProof' => $playerChart && $playerChart->getProof() !== null,
                'libs' => [],
            ];

            foreach ($chart->getLibs() as $lib) {
                $libId = $lib->getId();
                if ($libId === null) {
                    continue;
                }
                $existingValues[$libId] = [];

                // Find the corresponding PlayerChartLib
                if ($playerChart) {
                    foreach ($playerChart->getLibs() as $playerChartLib) {
                        if ($playerChartLib->getLibChart()->getId() === $libId) {
                            $parseValue = $playerChartLib->getParseValue();
                            foreach ($parseValue as $index => $val) {
                                $existingValues[$libId][$index] = $val['value'] ?? '';
                            }
                            break;
                        }
                    }
                }

                // Initialize empty values if no existing data
                if (empty($existingValues[$libId])) {
                    $parseMask = $lib->getType()->getParseMask();
                    foreach ($parseMask as $index => $part) {
                        $existingValues[$libId][$index] = '';
                    }
                }

                $originalData['libs'][$libId] = $existingValues[$libId];
            }

            $chartsData[] = [
                'chart' => $chart,
                'playerChart' => $playerChart,
                'existingValues' => $existingValues,
                'originalData' => $originalData,
            ];
        }

        return $chartsData;
    }
}
