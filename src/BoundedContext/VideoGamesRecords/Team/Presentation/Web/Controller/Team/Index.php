<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Web\Controller\Team;

use App\BoundedContext\VideoGamesRecords\Team\Application\DTO\TeamListFilter;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRepository;
use App\BoundedContext\VideoGamesRecords\Team\Presentation\Form\TeamFilterType;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class Index extends AbstractLocalizedController
{
    private const int LIMIT = 100;

    public function __construct(
        private readonly TeamRepository $teamRepository
    ) {
    }

    #[Route('/teams', name: 'vgr_team_index')]
    public function index(Request $request): Response
    {
        $sortBy = $request->query->get('sort', 'pointGame');
        $order = $request->query->get('order', 'DESC');
        $page = max(1, (int) $request->query->get('page', 1));

        $form = $this->createForm(TeamFilterType::class, ['sort' => $sortBy, 'order' => $order]);
        $form->handleRequest($request);

        $filterData = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $filterData = $form->getData();
        }

        $filter = $this->resolveFilter($filterData);

        $limit = self::LIMIT;
        $offset = ($page - 1) * $limit;

        $teams = $this->teamRepository->findForList($filter, $sortBy, $order, $limit, $offset);
        $totalTeams = $this->teamRepository->countForList($filter);
        $totalPages = max(1, (int) ceil($totalTeams / $limit));

        return $this->render('@VideoGamesRecordsTeam/team/index.html.twig', [
            'teams' => $teams,
            'currentSort' => $sortBy,
            'currentOrder' => $order,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalTeams' => $totalTeams,
            'limit' => $limit,
            'filterForm' => $form,
            'activeFilterCount' => $this->countActiveFilters($request),
            'hasActiveFilters' => !$filter->isEmpty(),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function resolveFilter(array $data): TeamListFilter
    {
        return new TeamListFilter(
            search: ($data['q'] ?? null) !== '' ? ($data['q'] ?? null) : null,
            status: ($data['status'] ?? null) !== '' ? ($data['status'] ?? null) : null,
            minPlayers: $data['minPlayers'] ?? null,
            pointsMin: $data['pointsMin'] ?? null,
            pointsMax: $data['pointsMax'] ?? null,
            hasBadges: (bool) ($data['hasBadges'] ?? false),
        );
    }

    private function countActiveFilters(Request $request): int
    {
        $count = 0;
        foreach (['q', 'status', 'minPlayers', 'pointsMin', 'pointsMax'] as $field) {
            if ($request->query->get($field)) {
                ++$count;
            }
        }

        if ($request->query->getBoolean('hasBadges')) {
            ++$count;
        }

        return $count;
    }
}
