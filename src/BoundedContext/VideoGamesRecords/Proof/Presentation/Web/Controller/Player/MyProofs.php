<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Web\Controller\Player;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class MyProofs extends AbstractController
{
    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly PlayerChartRepository $playerChartRepository
    ) {
    }

    #[Route('/my-proofs', name: 'vgr_my_proofs')]
    #[IsGranted('ROLE_USER')]
    public function __invoke(): Response
    {
        $player = $this->userProvider->getPlayer();

        if ($player === null) {
            throw $this->createAccessDeniedException();
        }

        $gameStatuses = $this->playerChartRepository->getStatusCountsByGameForPlayer($player);

        return $this->render('@VideoGamesRecordsProof/player/my_proofs.html.twig', [
            'gameStatuses' => $gameStatuses,
            'statuses' => PlayerChartStatusEnum::cases(),
        ]);
    }
}
