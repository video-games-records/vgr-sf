<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Web\Controller\Team;

use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Index extends AbstractLocalizedController
{
    public function __construct(
        private readonly TeamRepository $teamRepository
    ) {
    }

    #[Route('/teams', name: 'vgr_team_index')]
    public function index(Request $request): Response
    {
        $sortBy = $request->query->get('sort', 'pointGame');
        $order = $request->query->get('order', 'DESC');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 100;
        $offset = ($page - 1) * $limit;

        $teams = $this->teamRepository->findAllWithSort($sortBy, $order, $limit, $offset);
        $totalTeams = $this->teamRepository->countActiveTeams();
        $totalPages = (int) ceil($totalTeams / $limit);

        return $this->render('@VideoGamesRecordsTeam/team/index.html.twig', [
            'teams' => $teams,
            'currentSort' => $sortBy,
            'currentOrder' => $order,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalTeams' => $totalTeams,
        ]);
    }
}
