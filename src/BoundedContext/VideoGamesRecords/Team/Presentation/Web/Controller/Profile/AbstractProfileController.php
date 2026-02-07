<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Web\Controller\Profile;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRepository;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRequestRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractProfileController extends AbstractLocalizedController
{
    public function __construct(
        protected readonly TeamRepository $teamRepository,
        protected readonly PlayerRepository $playerRepository,
        protected readonly TeamRequestRepository $teamRequestRepository,
    ) {
    }

    protected function getTeam(int $id, string $slug): Team
    {
        $team = $this->teamRepository->find($id);

        if (!$team || $team->getSlug() !== $slug) {
            throw new NotFoundHttpException('Team not found');
        }

        return $team;
    }

    protected function canJoin(Team $team): bool
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if (!$team->isOpened()) {
            return false;
        }

        $player = $this->playerRepository->getPlayerFromUser($user);
        if ($player === null || $player->getTeam() !== null) {
            return false;
        }

        $activeRequest = $this->teamRequestRepository->findActiveByTeamAndPlayer($team, $player);

        return $activeRequest === null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getBaseParams(Team $team, string $currentTab): array
    {
        return [
            'team' => $team,
            'current_tab' => $currentTab,
            'canJoin' => $this->canJoin($team),
        ];
    }
}
