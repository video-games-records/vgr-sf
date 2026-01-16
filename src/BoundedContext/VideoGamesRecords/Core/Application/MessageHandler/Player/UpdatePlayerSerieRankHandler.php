<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\MessageHandler\Player;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\SharedKernel\Domain\Exception\EntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerSerieRank;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\RankingTools;

#[AsMessageHandler]
readonly class UpdatePlayerSerieRankHandler
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    /**
     * @param UpdatePlayerSerieRank $updatePlayerSerieRank
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function __invoke(UpdatePlayerSerieRank $updatePlayerSerieRank): void
    {
        /** @var Serie|null $serie */
        $serie = $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie')->find(
            $updatePlayerSerieRank->getSerieId()
        );
        if (null === $serie) {
            throw new EntityNotFoundException('Serie', $updatePlayerSerieRank->getSerieId());
        }

        // Delete old data
        $query = $this->em->createQuery(
            'DELETE App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerSerie us WHERE us.serie = :serie'
        );
        $query->setParameter('serie', $serie);
        $query->execute();

        // Select data
        $query = $this->em->createQuery("
            SELECT
                p.id as idPlayer,
                '' as rankPointChart,
                '' as rankMedal,
                SUM(pg.chartRank0) as chartRank0,
                SUM(pg.chartRank1) as chartRank1,
                SUM(pg.chartRank2) as chartRank2,
                SUM(pg.chartRank3) as chartRank3,
                SUM(pg.chartRank4) as chartRank4,
                SUM(pg.chartRank5) as chartRank5,
                SUM(pg.pointGame) as pointGame,
                SUM(pg.pointChart) as pointChart,
                SUM(pg.pointChartWithoutDlc) as pointChartWithoutDlc,
                SUM(pg.nbChart) as nbChart,
                SUM(pg.nbChartWithoutDlc) as nbChartWithoutDlc,
                SUM(pg.nbChartProven) as nbChartProven,
                SUM(pg.nbChartProvenWithoutDlc) as nbChartProvenWithoutDlc,
                COUNT(DISTINCT pg.game) as nbGame
            FROM App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGame pg
            JOIN pg.game g
            JOIN pg.player p
            WHERE g.serie = :serie
            GROUP BY p.id
            ORDER BY pointChart DESC");

        $query->setParameter('serie', $serie);
        $result = $query->getResult();

        $list = [];
        foreach ($result as $row) {
            $list[] = $row;
        }

        $list = RankingTools::addRank($list, 'rankPointChart', ['pointChart']);
        $list = RankingTools::order(
            $list,
            [
                'chartRank0' => SORT_DESC,
                'chartRank1' => SORT_DESC,
                'chartRank2' => SORT_DESC,
                'chartRank3' => SORT_DESC
            ]
        );
        $list = RankingTools::addRank(
            $list,
            'rankMedal',
            ['chartRank0', 'chartRank1', 'chartRank2', 'chartRank3', 'chartRank4', 'chartRank5']
        );

        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer([$normalizer]);


        foreach ($list as $row) {
            $playerSerie = $serializer->denormalize(
                $row,
                'App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerSerie'
            );
            $playerSerie->setPlayer(
                $this->em->getReference(
                    'App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player',
                    $row['idPlayer']
                )
            );
            $playerSerie->setSerie($serie);

            $this->em->persist($playerSerie);
            $this->em->flush();
        }

        // Update badges directly (was in PlayerSerieUpdatedSubscriber - now optimized)
        if ($serie->getBadge()) {
            // Get first place players from the ranking we just calculated
            $firstPlacePlayers = [];
            foreach ($list as $row) {
                if ($row['rankMedal'] === 1) {
                    $firstPlacePlayers[$row['idPlayer']] = 0;
                } else {
                    break; // Rankings are ordered, so no more first places
                }
            }

            $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge')
                ->updateBadge($firstPlacePlayers, $serie->getBadge());
        }
    }
}
