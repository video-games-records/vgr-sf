<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player\Profile;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractProfileController extends AbstractLocalizedController
{
    public function __construct(
        protected readonly PlayerRepository $playerRepository
    ) {
    }

    protected function getPlayer(int $id, string $slug): Player
    {
        $player = $this->playerRepository->find($id);

        if (!$player || $player->getSlug() !== $slug) {
            throw new NotFoundHttpException('Player not found');
        }

        return $player;
    }
}
