<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository;

use Doctrine\DBAL\Exception;
use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\LostPosition;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;

class LostPositionRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LostPosition::class);
    }


    /**
     * @param $player
     * @return int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getNbLostPosition(Player $player): mixed
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)');
        $this->wherePlayer($qb, $player);
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $player
     * @return int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getNbNewLostPosition(Player $player): mixed
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)');
        $this->wherePlayer($qb, $player);
        $qb->andWhere('l.createdAt > :now')
            ->setParameter('now', $player->getLastDisplayLostPosition());
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws Exception
     */
    public function purge(): void
    {
        $sql = "DELETE vgr_lostposition
        FROM vgr_lostposition
            INNER JOIN vgr_player_chart ON vgr_lostposition.player_id = vgr_player_chart.player_id AND vgr_lostposition.chart_id = vgr_player_chart.chart_id
        WHERE (vgr_player_chart.rank <= vgr_lostposition.old_rank)
        OR (vgr_player_chart.rank = 1 AND vgr_player_chart.nb_equal = 1 AND vgr_lostposition.old_rank = 0)";
        $this->getEntityManager()->getConnection()->executeStatement($sql);
    }


    /**
     * @param QueryBuilder $query
     * @param              $player
     */
    private function wherePlayer(QueryBuilder $query, Player $player): void
    {
        $query->where('l.player = :player')
            ->setParameter('player', $player);
    }
}
