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
}
