<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Web\Controller;

use App\BoundedContext\User\Application\Service\UserParameterService;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class HomeController extends AbstractLocalizedController
{
    public function __construct(
        private readonly Security $security,
        private readonly UserParameterService $userParameterService,
        private readonly PlayerRepository $playerRepository,
    ) {
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $dashboardType = $this->userParameterService->getHomeDashboard($user);

            if ($dashboardType === 'player') {
                $player = $this->playerRepository->getPlayerFromUser($user);

                if ($player !== null) {
                    return $this->render('@SharedKernel/dashboard.html.twig', [
                        'player' => $player,
                    ]);
                }
            }
        }

        return $this->render('@SharedKernel/home.html.twig');
    }
}
