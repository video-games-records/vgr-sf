<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends DefaultRepository<PlayerChart>
 */
class PlayerChartRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerChart::class);
    }

    /**
     * @return array<PlayerChart>
     */
    public function findLatest(int $limit = 5): array
    {
        $ids = $this->createQueryBuilder('pc')
            ->select('pc.id')
            ->orderBy('pc.lastUpdate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getSingleColumnResult();

        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('pc')
            ->join('pc.chart', 'c')
            ->join('c.group', 'g')
            ->join('g.game', 'ga')
            ->join('pc.player', 'p')
            ->leftJoin('pc.platform', 'plt')
            ->leftJoin('pc.libs', 'libs')
            ->leftJoin('libs.libChart', 'lc')
            ->leftJoin('lc.type', 'ct')
            ->addSelect('c', 'g', 'ga', 'p', 'plt', 'libs', 'lc', 'ct')
            ->where('pc.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('pc.lastUpdate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search PlayerCharts with filters and pagination
     *
     * @param array<int> $gameIds
     * @param array<int> $playerIds
     * @param array<int> $platformIds
     * @param array<PlayerChartStatusEnum> $statuses
     * @param int $page
     * @param int $limit
     * @return array{items: array<PlayerChart>, total: int, pages: int}
     */
    public function search(array $gameIds = [], array $playerIds = [], array $platformIds = [], array $statuses = [], ?string $rankOperator = null, ?int $rankValue = null, ?string $pointsOperator = null, ?int $pointsValue = null, bool $platinumOnly = false, int $page = 1, int $limit = 20): array
    {
        $hasRankFilter = $rankOperator !== null && $rankValue !== null;
        $hasPointsFilter = $pointsOperator !== null && $pointsValue !== null;

        if (empty($gameIds) && empty($playerIds) && empty($platformIds) && empty($statuses) && !$hasRankFilter && !$hasPointsFilter && !$platinumOnly) {
            return ['items' => [], 'total' => 0, 'pages' => 0];
        }

        $qb = $this->createQueryBuilder('pc')
            ->join('pc.chart', 'c')
            ->join('c.group', 'g')
            ->join('g.game', 'ga')
            ->join('pc.player', 'p')
            ->leftJoin('pc.platform', 'plt')
            ->leftJoin('pc.libs', 'libs')
            ->leftJoin('libs.libChart', 'lc')
            ->leftJoin('lc.type', 'ct')
            ->addSelect('c', 'g', 'ga', 'p', 'plt', 'libs', 'lc', 'ct');

        if (!empty($gameIds)) {
            $qb->andWhere('ga.id IN (:gameIds)')
               ->setParameter('gameIds', $gameIds);
        }

        if (!empty($playerIds)) {
            $qb->andWhere('p.id IN (:playerIds)')
               ->setParameter('playerIds', $playerIds);
        }

        if (!empty($platformIds)) {
            $qb->andWhere('plt.id IN (:platformIds)')
               ->setParameter('platformIds', $platformIds);
        }

        if (!empty($statuses)) {
            $qb->andWhere('pc.status IN (:statuses)')
               ->setParameter('statuses', $statuses);
        }

        if ($rankOperator !== null && $rankValue !== null) {
            $operator = match ($rankOperator) {
                'lt' => '<',
                'lte' => '<=',
                'eq' => '=',
                'gt' => '>',
                'gte' => '>=',
                default => '<=',
            };
            $qb->andWhere("pc.rank $operator :rankValue")
               ->setParameter('rankValue', $rankValue);
        }

        if ($pointsOperator !== null && $pointsValue !== null) {
            $operator = match ($pointsOperator) {
                'lt' => '<',
                'lte' => '<=',
                'eq' => '=',
                'gt' => '>',
                'gte' => '>=',
                default => '>=',
            };
            $qb->andWhere("pc.pointChart $operator :pointsValue")
               ->setParameter('pointsValue', $pointsValue);
        }

        if ($platinumOnly) {
            $qb->andWhere('pc.rank = 1')
               ->andWhere('pc.nbEqual = 1');
        }

        $qb->orderBy('ga.id', 'ASC')
           ->addOrderBy('g.id', 'ASC')
           ->addOrderBy('c.id', 'ASC')
           ->addOrderBy('pc.rank', 'ASC')
           ->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        $paginator = new Paginator($qb->getQuery(), fetchJoinCollection: true);
        $total = count($paginator);

        return [
            'items' => iterator_to_array($paginator),
            'total' => $total,
            'pages' => (int) ceil($total / $limit),
        ];
    }
}
