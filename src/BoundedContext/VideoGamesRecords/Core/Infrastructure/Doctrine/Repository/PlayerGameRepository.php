<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository;

use Doctrine\Persistence\ManagerRegistry;
use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGame;

/**
 * @extends DefaultRepository<PlayerGame>
 */
class PlayerGameRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerGame::class);
    }

    /**
     * @return PlayerGame[]
     */
    public function findByPlayerSorted(Player $player, string $sort = 'pointChart', string $order = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('pg')
            ->join('pg.game', 'g')
            ->where('pg.player = :player')
            ->setParameter('player', $player);

        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        switch ($sort) {
            case 'game':
                $qb->orderBy('g.libGameEn', $order);
                break;
            case 'medals':
                $qb->orderBy('pg.chartRank0', $order)
                   ->addOrderBy('pg.chartRank1', $order)
                   ->addOrderBy('pg.chartRank2', $order)
                   ->addOrderBy('pg.chartRank3', $order);
                break;
            case 'rank':
                $qb->orderBy('pg.rankPointChart', $order);
                break;
            case 'pointChart':
            default:
                $qb->orderBy('pg.pointChart', $order);
                break;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return PlayerGame[]
     */
    public function findAllByPlayerOrderedByLastUpdate(Player $player, int $limit = 0, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('pg')
            ->where('pg.player = :player')
            ->setParameter('player', $player)
            ->orderBy('pg.lastUpdate', 'DESC');

        if ($limit > 0) {
            $qb->setMaxResults($limit)->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return PlayerGame[]
     */
    public function findWherePlayerIsSecond(Player $player, int $limit = 5): array
    {
        return $this->createQueryBuilder('pg')
            ->where('pg.player = :player')
            ->andWhere('pg.rankPointChart = 2')
            ->andWhere('pg.pointChart > 0')
            ->setParameter('player', $player)
            ->orderBy('pg.pointChart', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $gameIds
     * @return array<int, int>
     */
    public function findLeaderPointsByGames(array $gameIds): array
    {
        if (empty($gameIds)) {
            return [];
        }

        $rows = $this->createQueryBuilder('pg')
            ->select('IDENTITY(pg.game) as gameId, pg.pointChart as points')
            ->where('pg.game IN (:gameIds)')
            ->andWhere('pg.rankPointChart = 1')
            ->setParameter('gameIds', $gameIds)
            ->getQuery()
            ->getArrayResult();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['gameId']] = (int) $row['points'];
        }

        return $map;
    }

    public function countByPlayer(Player $player): int
    {
        return (int) $this->createQueryBuilder('pg')
            ->select('COUNT(pg.player)')
            ->where('pg.player = :player')
            ->setParameter('player', $player)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
