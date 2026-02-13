<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Api\Controller;

use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class Autocomplete extends AbstractController
{
    public function __construct(
        private readonly TeamRepository $teamRepository,
    ) {
    }

    #[Route('/api/teams/autocomplete', name: 'vgr_api_team_autocomplete', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $q = $request->query->get('query', '');

        $teams = $this->teamRepository->autocomplete($q);

        $results = array_map(fn($team) => [
            'id' => $team->getId(),
            'text' => $team->getLibTeam(),
            'slug' => $team->getSlug(),
        ], $teams);

        return $this->json($results);
    }
}
