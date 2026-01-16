<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Manager;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\LostPositionRepository;

class LostPositionManager
{
    private LostPositionRepository $lostPositionRepository;


    public function __construct(LostPositionRepository $lostPositionRepository)
    {
        $this->lostPositionRepository = $lostPositionRepository;
    }

    /**
     * @param Player $player
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getNbLostPosition(Player $player): int
    {
        return $this->lostPositionRepository->getNbLostPosition($player);
    }

    /**
     * @param Player $player
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getNbNewLostPosition(Player $player): int
    {
        if ($player->getLastDisplayLostPosition() != null) {
            return $this->lostPositionRepository->getNbNewLostPosition($player);
        } else {
            return $this->getNbLostPosition($player);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function purge(): void
    {
        $this->lostPositionRepository->purge();
    }
}
