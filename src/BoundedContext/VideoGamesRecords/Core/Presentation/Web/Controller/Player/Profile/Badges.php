<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player\Profile;

use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\PlayerBadgeRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Badges extends AbstractProfileController
{
    public function __construct(
        PlayerRepository $playerRepository,
        private readonly PlayerBadgeRepository $playerBadgeRepository,
    ) {
        parent::__construct($playerRepository);
    }

    #[Route('/player/{id}-{slug}/badges', name: 'vgr_player_profile_badges', requirements: ['id' => '\d+'])]
    public function __invoke(int $id, string $slug): Response
    {
        $player = $this->getPlayer($id, $slug);

        return $this->render('@VideoGamesRecordsCore/player/profile/badges.html.twig', [
            'player' => $player,
            'masterBadgesData' => $this->playerBadgeRepository->getMasterBadgesDataForPlayer($player),
            'serieBadgesData' => $this->playerBadgeRepository->getSerieBadgesDataForPlayer($player),
            'platformBadgesData' => $this->playerBadgeRepository->getPlatformBadgesDataForPlayer($player),
            'countryBadgesData' => $this->playerBadgeRepository->getCountryBadgesDataForPlayer($player),
            'forumBadgesData' => $this->playerBadgeRepository->getForumBadgesDataForPlayer($player),
            'connexionBadgesData' => $this->playerBadgeRepository->getConnexionBadgesDataForPlayer($player),
            'chartBadgesData' => $this->playerBadgeRepository->getChartBadgesDataForPlayer($player),
            'proofBadgesData' => $this->playerBadgeRepository->getProofBadgesDataForPlayer($player),
            'donationBadgesData' => $this->playerBadgeRepository->getDonationBadgesDataForPlayer($player),
            'current_tab' => 'badges',
        ]);
    }
}
