<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
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
     * @return array<PlayerChart>
     */
    public function findLatestBySerie(Serie $serie, int $limit = 30): array
    {
        $ids = $this->createQueryBuilder('pc')
            ->select('pc.id')
            ->join('pc.chart', 'c')
            ->join('c.group', 'g')
            ->join('g.game', 'ga')
            ->where('ga.serie = :serie')
            ->setParameter('serie', $serie)
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
     * Find latest scores within a time period with pagination
     *
     * @return Paginator<PlayerChart>
     */
    public function findLatestByPeriod(int $days, int $page = 1, int $limit = 50): Paginator
    {
        $since = new \DateTime("-{$days} days");

        $qb = $this->createQueryBuilder('pc')
            ->join('pc.chart', 'c')
            ->join('c.group', 'g')
            ->join('g.game', 'ga')
            ->join('pc.player', 'p')
            ->leftJoin('pc.platform', 'plt')
            ->leftJoin('pc.libs', 'libs')
            ->leftJoin('libs.libChart', 'lc')
            ->leftJoin('lc.type', 'ct')
            ->addSelect('c', 'g', 'ga', 'p', 'plt', 'libs', 'lc', 'ct')
            ->where('pc.lastUpdate >= :since')
            ->setParameter('since', $since)
            ->orderBy('pc.lastUpdate', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);


        return new Paginator($qb->getQuery(), fetchJoinCollection: true);
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

    /**
     * @return array<array{game: Game, statuses: array<string, int>}>
     */
    public function getStatusCountsByGameForPlayer(Player $player): array
    {
        $rows = $this->createQueryBuilder('pc')
            ->select('IDENTITY(g.game) AS game_id, pc.status AS status, COUNT(pc.id) AS cnt')
            ->join('pc.chart', 'c')
            ->join('c.group', 'g')
            ->where('pc.player = :player')
            ->setParameter('player', $player)
            ->groupBy('game_id, pc.status')
            ->getQuery()
            ->getArrayResult();

        if (empty($rows)) {
            return [];
        }

        $gameIds = array_unique(array_column($rows, 'game_id'));

        $games = $this->getEntityManager()
            ->getRepository(Game::class)
            ->createQueryBuilder('ga')
            ->where('ga.id IN (:ids)')
            ->setParameter('ids', $gameIds)
            ->orderBy('ga.libGameEn', 'ASC')
            ->getQuery()
            ->getResult();

        $gamesById = [];
        foreach ($games as $game) {
            $gamesById[$game->getId()] = $game;
        }

        $grouped = [];
        foreach ($rows as $row) {
            $gameId = $row['game_id'];
            if (!isset($grouped[$gameId])) {
                $grouped[$gameId] = [];
            }
            $status = $row['status'] instanceof PlayerChartStatusEnum
                ? $row['status']->value
                : $row['status'];
            $grouped[$gameId][$status] = (int) $row['cnt'];
        }

        $result = [];
        foreach ($gamesById as $gameId => $game) {
            if (isset($grouped[$gameId])) {
                $result[] = [
                    'game' => $game,
                    'statuses' => $grouped[$gameId],
                ];
            }
        }

        return $result;
    }

    /**
     * @return array<array{group: \App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group, playerCharts: array<PlayerChart>}>
     */
    public function findByPlayerAndGameGroupedByGroup(Player $player, Game $game): array
    {
        $playerCharts = $this->createQueryBuilder('pc')
            ->join('pc.chart', 'c')
            ->join('c.group', 'g')
            ->leftJoin('pc.proof', 'proof')
            ->leftJoin('proof.picture', 'picture')
            ->leftJoin('proof.video', 'video')
            ->leftJoin('pc.libs', 'libs')
            ->leftJoin('libs.libChart', 'lc')
            ->leftJoin('lc.type', 'ct')
            ->addSelect('c', 'g', 'proof', 'picture', 'video', 'libs', 'lc', 'ct')
            ->where('pc.player = :player')
            ->andWhere('g.game = :game')
            ->setParameter('player', $player)
            ->setParameter('game', $game)
            ->orderBy('g.libGroupEn', 'ASC')
            ->addOrderBy('c.libChartEn', 'ASC')
            ->getQuery()
            ->getResult();

        $grouped = [];
        foreach ($playerCharts as $pc) {
            $group = $pc->getChart()->getGroup();
            $groupId = $group->getId();
            if (!isset($grouped[$groupId])) {
                $grouped[$groupId] = [
                    'group' => $group,
                    'playerCharts' => [],
                ];
            }
            $grouped[$groupId]['playerCharts'][] = $pc;
        }

        return array_values($grouped);
    }
}
