<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\DataProvider\Ranking;

use App\BoundedContext\VideoGamesRecords\Shared\Application\DataProvider\Ranking\AbstractRankingProvider;

class PlayerCountryRankingProvider extends AbstractRankingProvider
{
    /**
     * @param int|null $id
     * @param array<string, mixed> $options
     * @return array<\App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player>
     */
    public function getRankingPoints(?int $id = null, array $options = []): array
    {
        $country = $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country')->find($id);
        if (null === $country) {
            return [];
        }

        $maxRank = $options['maxRank'] ?? null;

        $query = $this->em->createQueryBuilder()
            ->select('p')
            ->from('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player', 'p')
            ->where('(p.country = :country)')
            ->setParameter('country', $country)
            ->orderBy('p.rankCountry');

        if ($maxRank !== null) {
            $query->andWhere('p.rankCountry <= :maxRank')
                ->setParameter('maxRank', $maxRank);
        } else {
            $query->setMaxResults($maxRank);
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
