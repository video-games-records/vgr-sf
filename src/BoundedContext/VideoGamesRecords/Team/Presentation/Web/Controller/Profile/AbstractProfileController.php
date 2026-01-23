<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Web\Controller\Profile;

use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractProfileController extends AbstractLocalizedController
{
    public function __construct(
        protected readonly TeamRepository $teamRepository
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
}
