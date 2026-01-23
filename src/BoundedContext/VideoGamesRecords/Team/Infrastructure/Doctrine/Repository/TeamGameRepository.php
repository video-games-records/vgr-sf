<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository;

use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamGame;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;

/**
 * @extends DefaultRepository<TeamGame>
 */
class TeamGameRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamGame::class);
    }

    /**
     * @return TeamGame[]
     */
    public function findByTeamSorted(Team $team, string $sort = 'pointChart', string $order = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('tg')
            ->join('tg.game', 'g')
            ->where('tg.team = :team')
            ->setParameter('team', $team);

        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        switch ($sort) {
            case 'game':
                $qb->orderBy('g.libGameEn', $order);
                break;
            case 'medals':
                $qb->orderBy('tg.chartRank0', $order)
                   ->addOrderBy('tg.chartRank1', $order)
                   ->addOrderBy('tg.chartRank2', $order)
                   ->addOrderBy('tg.chartRank3', $order);
                break;
            case 'rank':
                $qb->orderBy('tg.rankPointChart', $order);
                break;
            case 'pointChart':
            default:
                $qb->orderBy('tg.pointChart', $order);
                break;
        }

        return $qb->getQuery()->getResult();
    }
}
