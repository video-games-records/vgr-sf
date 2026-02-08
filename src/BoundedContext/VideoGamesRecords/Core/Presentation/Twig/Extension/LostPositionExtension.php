<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Twig\Extension;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\LostPositionRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LostPositionExtension extends AbstractExtension
{
    public function __construct(
        private readonly LostPositionRepository $lostPositionRepository,
        private readonly PlayerRepository $playerRepository,
        private readonly Security $security,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_new_lost_positions_count', [$this, 'getNewLostPositionsCount']),
        ];
    }

    public function getNewLostPositionsCount(): int
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return 0;
        }

        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            return 0;
        }

        return (int) $this->lostPositionRepository->getNbNewLostPosition($player);
    }
}
