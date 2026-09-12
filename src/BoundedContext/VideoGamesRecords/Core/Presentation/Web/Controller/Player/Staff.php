<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class Staff extends AbstractLocalizedController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository
    ) {
    }

    #[Route('/staff', name: 'vgr_staff_index')]
    public function index(): Response
    {
        $staff = $this->playerRepository->findStaff();

        return $this->render('@VideoGamesRecordsCore/player/staff.html.twig', [
            'staff' => $staff,
        ]);
    }
}
