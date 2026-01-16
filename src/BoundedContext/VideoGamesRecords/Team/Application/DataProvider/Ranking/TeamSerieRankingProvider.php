<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\DataProvider\Ranking;

use Doctrine\ORM\Exception\ORMException;
use App\BoundedContext\VideoGamesRecords\Shared\Application\DataProvider\Ranking\AbstractRankingProvider;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;

class TeamSerieRankingProvider extends AbstractRankingProvider
{
    /**
     * @param int|null $id
     * @param array<string, mixed> $options
     * @return array<\App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamSerie>
     */
    public function getRankingPoints(?int $id = null, array $options = []): array
    {
        $serie = $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie')->find($id);
        if (null == $serie) {
            return [];
        }

        $maxRank = $options['maxRank'] ?? null;
        $limit = $options['limit'] ?? null;

        $query = $this->em->createQueryBuilder()
            ->select('ts')
            ->from('App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamSerie', 'ts')
            ->join('ts.team', 't')
            ->addSelect('t')
            ->orderBy('ts.rankPointChart');

        $query->where('ts.serie = :serie')
            ->setParameter('serie', $serie);

        if (null !== $maxRank) {
            $query->andWhere('ts.rankPointChart <= :maxRank');
            $query->setParameter('maxRank', $maxRank);
        }

        if (null !== $limit) {
            $query->setMaxResults($limit);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param int|null $id
     * @param array<string, mixed> $options
     * @return array<\App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamSerie>
     * @throws ORMException
     */
    public function getRankingMedals(?int $id = null, array $options = []): array
    {
        /** @var Serie|null $serie */
        $serie = $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie')->find($id);
        if (null === $serie) {
            return [];
        }

        $maxRank = $options['maxRank'] ?? null;
        $limit = $options['limit'] ?? null;

        $query = $this->em->createQueryBuilder()
            ->select('ts')
            ->from('App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamSerie', 'ts')
            ->join('ts.team', 't')
            ->addSelect('t')
            ->orderBy('ts.rankMedal');

        $query->where('ts.serie = :serie')
            ->setParameter('serie', $serie);

        if (null !== $maxRank) {
            $query->andWhere('ts.rankPointChart <= :maxRank');
            $query->setParameter('maxRank', $maxRank);
        }

        if (null !== $limit) {
            $query->setMaxResults($limit);
        }

        return $query->getQuery()->getResult();
    }
}
