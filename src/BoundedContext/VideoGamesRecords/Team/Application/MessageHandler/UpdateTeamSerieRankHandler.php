<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\MessageHandler;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\SharedKernel\Domain\Exception\EntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\BoundedContext\VideoGamesRecords\Team\Application\Message\UpdateTeamSerieRank;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\RankingTools;

#[AsMessageHandler]
readonly class UpdateTeamSerieRankHandler
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    /**
     * @throws ORMException
     * @throws ExceptionInterface|EntityNotFoundException
     */
    public function __invoke(UpdateTeamSerieRank $updateTeamSerieRank): void
    {
        /** @var Serie|null $serie */
        $serie = $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie')
            ->find($updateTeamSerieRank->getSerieId());

        if (null === $serie) {
            throw new EntityNotFoundException('Serie', $updateTeamSerieRank->getSerieId());
        }

        // Delete old data
        $query = $this->em
            ->createQuery(
                'DELETE App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamSerie us WHERE us.serie = :serie'
            );
        $query->setParameter('serie', $serie);
        $query->execute();

        // Select data
        $query = $this->em->createQuery("
            SELECT
                t.id as idTeam,
                '' as rankPointChart,
                '' as rankMedal,
                SUM(tg.chartRank0) as chartRank0,
                SUM(tg.chartRank1) as chartRank1,
                SUM(tg.chartRank2) as chartRank2,
                SUM(tg.chartRank3) as chartRank3,
                SUM(tg.pointGame) as pointGame,
                SUM(tg.pointChart) as pointChart
            FROM App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamGame tg
            JOIN tg.game g
            JOIN tg.team t
            WHERE g.serie = :serie
            GROUP BY t.id
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
        $list = RankingTools::addRank($list, 'rankMedal', ['chartRank0', 'chartRank1', 'chartRank2', 'chartRank3']);

        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer([$normalizer]);


        foreach ($list as $row) {
            $teamSerie = $serializer->denormalize(
                $row,
                'App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamSerie'
            );
            $teamSerie->setTeam(
                $this->em->getReference('App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team', $row['idTeam'])
            );
            $teamSerie->setSerie($serie);

            $this->em->persist($teamSerie);
        }

        $this->em->flush();

        // Update badges directly (was in TeamSerieUpdatedSubscriber - now optimized)
        if ($serie->getBadge()) {
            // Get first place teams from the ranking we just calculated
            $firstPlaceTeams = [];
            foreach ($list as $row) {
                if ($row['rankMedal'] === 1) {
                    $firstPlaceTeams[$row['idTeam']] = 0;
                } else {
                    break; // Rankings are ordered, so no more first places
                }
            }

            $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\TeamBadge')
                ->updateBadge($firstPlaceTeams, $serie->getBadge());
        }
    }
}
