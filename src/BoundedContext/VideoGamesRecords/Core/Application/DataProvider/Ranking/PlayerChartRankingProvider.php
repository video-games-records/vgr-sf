<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DataProvider\Ranking;

use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use App\BoundedContext\VideoGamesRecords\Shared\Application\DataProvider\Ranking\AbstractRankingProvider;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;

class PlayerChartRankingProvider extends AbstractRankingProvider
{
    public const string ORDER_BY_RANK = 'RANK';
    public const string ORDER_BY_SCORE = 'SCORE';

    /**
     * @param int|null $id
     * @param array<string, mixed> $options
     * @return array<\App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart>
     * @throws ORMException
     */
    public function getRankingPoints(?int $id = null, array $options = []): array
    {
        $chart = $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart')->find($id);
        if (null === $chart) {
            return [];
        }

        $maxRank = $options['maxRank'] ?? null;
        $player = $this->getPlayer($options['user'] ?? null);
        $team = !empty($options['idTeam']) ? $this->em->getReference('App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team', $options['idTeam']) : null;

        $query = $this->em->createQueryBuilder()
            ->select('pc')
            ->from('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart', 'pc')
            ->join('pc.player', 'p')
            ->addSelect('p')
            ->orderBy('pc.rank');

        $query->where('pc.chart = :chart')
            ->setParameter('chart', $chart);

        if ($team != null) {
            $query->andWhere('(p.team = :team)')
                ->setParameter('team', $team);
        } elseif (($maxRank !== null) && ($player !== null)) {
            $query->andWhere('(pg.rankPointChart <= :maxRank OR pc.player= :player OR p.id IN (:friends))')
                ->setParameter('maxRank', $maxRank)
                ->setParameter('player', $player)
                ->setParameter('friends', $player->getFriends());
        } elseif ($maxRank !== null) {
            $query->andWhere('pc.rank <= :maxRank')
                ->setParameter('maxRank', $maxRank);
        } else {
            $query->setMaxResults(100);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param int|null $id
     * @param array<string, mixed> $options
     * @return array<mixed>
     */
    public function getRankingMedals(?int $id = null, array $options = []): array
    {
        return [];
    }

    /**
     * @param Chart $chart
     * @param array<string, mixed> $options
     * @return array<\App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart>
     * @throws ORMException
     */
    public function getRanking(Chart $chart, array $options = []): array
    {
        $maxRank = $options['maxRank'] ?? null;
        $player = $this->getPlayer($options['user'] ?? null);

        $orderBy = $options['orderBy'] ?? self::ORDER_BY_RANK;
        $queryBuilder = $this->getRankingBaseQuery($chart, $orderBy);
        $queryBuilder->andWhere('pc.status != :unproved')
            ->setParameter('unproved', 'unproved');

        if (null !== $maxRank && null !== $player) {
            $rank = $this->getRank($player, $chart);
            if ($rank) {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder->expr()->orX(
                            '(pc.rank <= :maxRank)',
                            '(pc.rank IS NULL)',
                            '(:min <= pc.rank) AND (pc.rank <= :max)',
                            '(pc.player = :player)'
                        )
                    )
                    ->setParameter('maxRank', $maxRank)
                    ->setParameter('player', $player)
                    ->setParameter(':min', $rank - 5)
                    ->setParameter(':max', $rank + 5);
            } else {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder->expr()->orX('(pc.rank <= :maxRank)', '(pc.rank IS NULL)', 'pc.player = :player')
                    )
                    ->setParameter('maxRank', $maxRank)
                    ->setParameter('player', $player);
            }
        } elseif (null !== $maxRank) {
            $queryBuilder
                ->andWhere('pc.rank <= :maxRank')
                ->setParameter('maxRank', $maxRank);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Chart $chart
     * @return array<\App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart>
     */
    public function getRankingDisabled(Chart $chart): array
    {
        $queryBuilder = $this->getRankingBaseQuery($chart, self::ORDER_BY_SCORE);
        $queryBuilder
            ->andWhere('pc.status = :unproved')
            ->setParameter('unproved', 'unproved');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Chart  $chart
     * @param string $orderBy
     * @return QueryBuilder
     */
    private function getRankingBaseQuery(Chart $chart, string $orderBy = self::ORDER_BY_RANK): QueryBuilder
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('pc')
            ->from('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart', 'pc')
            ->innerJoin('pc.player', 'p')
            ->addSelect('p')
            ->innerJoin('pc.chart', 'c')
            ->leftJoin('pc.proof', 'proof')
            ->addSelect('proof')
            ->leftJoin('pc.platform', 'platform')
            ->addSelect('platform')
            ->where('c.id = :idChart')
            ->setParameter('idChart', $chart->getId());

        if (self::ORDER_BY_RANK === $orderBy) {
            $queryBuilder->orderBy('pc.rank', 'ASC')
                ->addOrderBy('pc.lastUpdate', 'ASC');
        }

        foreach ($chart->getLibs() as $lib) {
            $key             = 'value_' . $lib->getId();
            $alias           = 'pcl_' . $lib->getId();
            $subQueryBuilder = $this->em->createQueryBuilder()
                ->select(sprintf('%s.value', $alias))
                ->from('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChartLib', $alias)
                ->where(sprintf('%s.libChart = :%s', $alias, $key))
                ->andWhere(sprintf('%s.playerChart = pc', $alias))
                ->setParameter($key, $lib);

            $queryBuilder
                ->addSelect(sprintf('(%s) as %s', $subQueryBuilder->getQuery()->getDQL(), $key))
                ->setParameter($key, $lib);
            if (self::ORDER_BY_SCORE === $orderBy) {
                $queryBuilder->addOrderBy($key, $lib->getType()->getOrderBy());
            }
        }

        return $queryBuilder;
    }

    /**
     * @param Player $player
     * @param Chart  $chart
     * @return int|null
     */
    private function getRank(Player $player, Chart $chart): ?int
    {
        $query = $this->em->createQueryBuilder()
            ->from('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart', 'pc')
            ->select('pc.rank')
            ->where('pc.player = :player')
            ->setParameter('player', $player)
            ->andWhere('pc.chart = :chart')
            ->setParameter('chart', $chart);

        try {
            return $query->getQuery()->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return null;
        }
    }
}
