<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Presentation\Web\Controller;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Repository\PlatformConnectionRepositoryInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[IsGranted('ROLE_USER')]
class LinkedAccountsController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly PlayerRepository $playerRepository,
        private readonly PlatformConnectionRepositoryInterface $platformConnectionRepository,
    ) {
    }

    #[Route('/profile/platforms', name: 'app_profile_platforms', methods: ['GET'])]
    public function __invoke(): Response
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            throw new NotFoundHttpException('Player not found.');
        }

        $connections = [];
        foreach ($this->platformConnectionRepository->findByPlayer($player) as $connection) {
            $connections[$connection->getPlatform()->value] = $connection;
        }

        return $this->render('@VideoGamesRecordsGamingPlatform/profile/linked_accounts.html.twig', [
            'connections' => $connections,
            'allPlatforms' => PlatformEnum::supported(),
        ]);
    }
}
