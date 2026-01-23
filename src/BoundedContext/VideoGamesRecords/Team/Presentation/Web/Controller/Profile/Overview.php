<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Web\Controller\Profile;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Overview extends AbstractProfileController
{
    #[Route('/team/{id}-{slug}', name: 'vgr_team_profile_overview', requirements: ['id' => '\d+'])]
    public function __invoke(int $id, string $slug): Response
    {
        $team = $this->getTeam($id, $slug);

        return $this->render('@VideoGamesRecordsTeam/profile/overview.html.twig', [
            'team' => $team,
            'current_tab' => 'overview',
        ]);
    }
}
