<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player\Profile;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class Overview extends AbstractProfileController
{
    #[Route('/player/{id}-{slug}', name: 'vgr_player_profile_overview', requirements: ['id' => '\d+'])]
    public function __invoke(int $id, string $slug): Response
    {
        $player = $this->getPlayer($id, $slug);

        return $this->render('@VideoGamesRecordsCore/player/profile/overview.html.twig', [
            'player' => $player,
            'current_tab' => 'overview',
        ]);
    }
}
