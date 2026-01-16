<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\DataProvider\Ranking;

use Doctrine\ORM\Exception\ORMException;
use App\BoundedContext\VideoGamesRecords\Shared\Application\DataProvider\Ranking\AbstractRankingProvider;

class TeamChartRankingProvider extends AbstractRankingProvider
{
    /**
     * @param int|null $id
     * @param array<string, mixed> $options
     * @return array<\App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamChart>
     * @throws ORMException
     */
    public function getRankingPoints(?int $id = null, array $options = []): array
    {
        $chart = $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart')->find($id);
        if (null === $chart) {
            return [];
        }

        $maxRank = $options['maxRank'] ?? null;
        $team = $this->getTeam($options['user'] ?? null);

        $query = $this->em->createQueryBuilder()
            ->select('tc')
            ->from('App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamChart', 'tc')
            ->join('tc.team', 't')
            ->addSelect('t')
            ->orderBy('tc.rankPointChart');

        $query->where('tc.chart = :chart')
            ->setParameter('chart', $chart);

        if (($maxRank !== null) && ($team !== null)) {
            $query->andWhere('(tc.rankPointChart <= :maxRank OR tc.team = :team)')
                ->setParameter('maxRank', $maxRank)
                ->setParameter('team', $team);
        } elseif ($maxRank !== null) {
            $query->andWhere('tc.rankPointChart <= :maxRank')
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
}
