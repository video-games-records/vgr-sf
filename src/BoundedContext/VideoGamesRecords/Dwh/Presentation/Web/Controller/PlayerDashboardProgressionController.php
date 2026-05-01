<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Presentation\Web\Controller;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Dwh\Infrastructure\Doctrine\Repository\PlayerRepository as DwhPlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

class PlayerDashboardProgressionController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly PlayerRepository $playerRepository,
        private readonly DwhPlayerRepository $dwhPlayerRepository,
    ) {
    }

    public function __invoke(): Response
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return new Response('');
        }

        $player = $this->playerRepository->getPlayerFromUser($user);
        $playerId = $player?->getId();

        if ($player === null || $playerId === null) {
            return new Response('');
        }

        $latest = $this->dwhPlayerRepository->findLatestForPlayer($playerId);

        if ($latest === null) {
            return new Response('');
        }

        $latestDate = new \DateTimeImmutable($latest->getDate());

        $date7 = $latestDate->modify('-7 days')->format('Y-m-d');
        $date30 = $latestDate->modify('-30 days')->format('Y-m-d');

        $snapshot7 = $this->dwhPlayerRepository->findForPlayerAtDate($playerId, $date7);
        $snapshot30 = $this->dwhPlayerRepository->findForPlayerAtDate($playerId, $date30);

        return $this->render('@VideoGamesRecordsDwh/player/_dashboard_progression.html.twig', [
            'latest' => $latest,
            'snapshot7' => $snapshot7,
            'snapshot30' => $snapshot30,
        ]);
    }
}
