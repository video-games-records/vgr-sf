<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player\Profile;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Proofs extends AbstractProfileController
{
    #[Route('/player/{id}-{slug}/proofs', name: 'vgr_player_profile_proofs', requirements: ['id' => '\d+'])]
    public function __invoke(int $id, string $slug): Response
    {
        $player = $this->getPlayer($id, $slug);

        return $this->render('@VideoGamesRecordsCore/player/profile/proofs.html.twig', [
            'player' => $player,
            'current_tab' => 'proofs',
        ]);
    }
}
