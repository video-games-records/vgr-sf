<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;

class ScoreManager
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    /**
     * @param Player $player
     * @param int $idGame
     * @param int $idPlatform
     * @return void
     * @throws ORMException
     */
    public function updatePlatform(Player $player, int $idGame, int $idPlatform): void
    {
        $qb = $this->em->createQueryBuilder();
        $query = $qb->update('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart', 'pc')
            ->set('pc.platform', ':platform')
            ->where('pc.player = :player')
            ->setParameter('platform', $idPlatform)
            ->setParameter('player', $player)
            ->andWhere('pc.chart IN (
                            SELECT c FROM App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart c
                            join c.group g
                        WHERE g.game = :game)')
            ->setParameter('game', $idGame);
        $query->getQuery()->execute();
    }

    /**
     * @param Player $player
     * @param Game   $game
     * @return Platform|null
     * @throws NonUniqueResultException
     * @throws ORMException
     */
    public function getPlatform(Player $player, Game $game): ?Platform
    {
        $qb = $this->em->createQueryBuilder();
        $query = $qb->select('pc')
            ->from('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart', 'pc')
            ->innerJoin('pc.chart', 'c')
            ->innerJoin('c.group', 'g')
            ->where('pc.player = :player')
            ->setParameter('player', $player)
            ->andWhere('g.game = :game')
            ->setParameter('game', $game)
            ->andWhere('pc.platform IS NOT NULL')
            ->orderBy("pc.lastUpdate", "DESC")
            ->setMaxResults(1);

        $playerChart = $query->getQuery()->getOneOrNullResult();

        return $playerChart?->getPlatform();
    }
}
