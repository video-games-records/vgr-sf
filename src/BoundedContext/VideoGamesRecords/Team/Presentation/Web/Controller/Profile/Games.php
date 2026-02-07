<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Web\Controller\Profile;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamGameRepository;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRepository;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRequestRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Games extends AbstractProfileController
{
    public function __construct(
        TeamRepository $teamRepository,
        PlayerRepository $playerRepository,
        TeamRequestRepository $teamRequestRepository,
        private readonly TeamGameRepository $teamGameRepository
    ) {
        parent::__construct($teamRepository, $playerRepository, $teamRequestRepository);
    }

    #[Route('/team/{id}-{slug}/games', name: 'vgr_team_profile_games', requirements: ['id' => '\d+'])]
    public function __invoke(int $id, string $slug, Request $request): Response
    {
        $team = $this->getTeam($id, $slug);

        $allowedSorts = ['game', 'medals', 'pointChart', 'rank'];
        $sort = $request->query->get('sort', 'pointChart');
        $order = $request->query->get('order', 'DESC');

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'pointChart';
        }

        $teamGames = $this->teamGameRepository->findByTeamSorted($team, $sort, $order);

        return $this->render('@VideoGamesRecordsTeam/profile/games.html.twig', array_merge(
            $this->getBaseParams($team, 'games'),
            [
                'teamGames' => $teamGames,
                'currentSort' => $sort,
                'currentOrder' => $order,
            ]
        ));
    }
}
