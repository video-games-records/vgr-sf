<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\DataProvider\Ranking;

use Doctrine\ORM\Exception\ORMException;
use App\BoundedContext\VideoGamesRecords\Shared\Application\DataProvider\Ranking\AbstractRankingProvider;

class TeamGameRankingProvider extends AbstractRankingProvider
{
    /**
     * @param int|null $id
     * @param array<string, mixed> $options
     * @return array<\App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamGame>
     */
    public function getRankingPoints(?int $id = null, array $options = []): array
    {
        $game = $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game')->find($id);
        if (null === $game) {
            return [];
        }

        $maxRank = $options['maxRank'] ?? null;
        $team = $this->getTeam($options['user'] ?? null);

        $query = $this->em->createQueryBuilder()
            ->select('tg')
            ->from('App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamGame', 'tg')
            ->join('tg.team', 't')
            ->addSelect('t')
            ->orderBy('tg.rankPointChart');

        $query->where('tg.game = :game')
            ->setParameter('game', $game);

        if (($maxRank !== null) && ($team !== null)) {
            $query->andWhere('(tg.rankPointChart <= :maxRank OR tg.team = :team)')
                ->setParameter('maxRank', $maxRank)
                ->setParameter('team', $team);
        } elseif ($maxRank !== null) {
            $query->andWhere('tg.rankPointChart <= :maxRank')
                ->setParameter('maxRank', $maxRank);
        } else {
            $query->setMaxResults(100);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param int|null $id
     * @param array<string, mixed> $options
     * @return array<\App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamGame>
     * @throws ORMException
     */
    public function getRankingMedals(?int $id = null, array $options = []): array
    {
        $game = $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game')->find($id);
        if (null === $game) {
            return [];
        }

        $maxRank = $options['maxRank'] ?? null;
        $team = $this->getTeam($options['user'] ?? null);

        $query = $this->em->createQueryBuilder()
            ->select('tg')
            ->from('App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamGame', 'tg')
            ->join('tg.team', 't')
            ->addSelect('t')
            ->orderBy('tg.rankMedal');

        $query->where('tg.game = :game')
            ->setParameter('game', $game);

        if (($maxRank !== null) && ($team !== null)) {
            $query->andWhere('(tg.rankMedal <= :maxRank OR tg.team = :team)')
                ->setParameter('maxRank', $maxRank)
                ->setParameter('team', $team);
        } elseif ($maxRank !== null) {
            $query->andWhere('tg.rankMedal <= :maxRank')
                ->setParameter('maxRank', $maxRank);
        } else {
            $query->setMaxResults(100);
        }

        return $query->getQuery()->getResult();
    }
}
