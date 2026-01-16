<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\MessageHandler;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\SharedKernel\Domain\Exception\EntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\BoundedContext\VideoGamesRecords\Team\Application\Message\UpdateTeamChartRank;
use App\BoundedContext\VideoGamesRecords\Team\Application\Message\UpdateTeamGroupRank;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\RankingTools;
use Zenstruck\Messenger\Monitor\Stamp\DescriptionStamp;

#[AsMessageHandler]
readonly class UpdateTeamChartRankHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @throws ORMException
     * @throws ExceptionInterface|EntityNotFoundException
     */
    public function __invoke(UpdateTeamChartRank $updateTeamChartRank): void
    {
        /** @var Chart|null $chart */
        $chart = $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart')
            ->find($updateTeamChartRank->getChartId());

        if (null == $chart) {
            throw new EntityNotFoundException('Chart', $updateTeamChartRank->getChartId());
        }

        //----- delete
        $query = $this->em
            ->createQuery('DELETE App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamChart tc WHERE tc.chart = :chart');
        $query->setParameter('chart', $chart);
        $query->execute();

        $query = $this->em->createQuery("
            SELECT pc
            FROM App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart pc
            JOIN pc.player p
            JOIN p.team t
            WHERE pc.chart = :chart
            ORDER BY pc.pointChart DESC");

        $query->setParameter('chart', $chart);
        $result = $query->getResult();

        $list = [];
        foreach ($result as $playerChart) {
            $team = $playerChart->getPlayer()->getTeam();

            $idTeam = $team->getId();
            if (!isset($list[$idTeam])) {
                $list[$idTeam] = [
                    'idTeam' => $playerChart->getPlayer()->getTeam()->getId(),
                    'nbPlayer' => 1,
                    'pointChart' => $playerChart->getPointChart(),
                    'chartRank0' => 0,
                    'chartRank1' => 0,
                    'chartRank2' => 0,
                    'chartRank3' => 0,
                ];
            } elseif ($list[$idTeam]['nbPlayer'] < 5) {
                $list[$idTeam]['nbPlayer']   += 1;
                $list[$idTeam]['pointChart'] += $playerChart->getPointChart();
            }
        }

        //----- add some data
        $list = array_values($list);
        $list = RankingTools::order($list, ['pointChart' => SORT_DESC]);
        $list = RankingTools::addRank($list, 'rankPointChart', ['pointChart'], true);

        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer([$normalizer]);

        $nbTeam = count($list);

        foreach ($list as $row) {
            //----- add medals
            if ($row['rankPointChart'] == 1 && $row['nbEqual'] == 1 && $nbTeam > 1) {
                $row['chartRank0'] = 1;
                $row['chartRank1'] = 1;
            } elseif ($row['rankPointChart'] == 1 && $row['nbEqual'] == 1 && $nbTeam == 1) {
                $row['chartRank1'] = 1;
            } elseif ($row['rankPointChart'] == 1 && $row['nbEqual'] > 1) {
                $row['chartRank1'] = 1;
            } elseif ($row['rankPointChart'] == 2) {
                $row['chartRank2'] = 1;
            } elseif ($row['rankPointChart'] == 3) {
                $row['chartRank3'] = 1;
            }

            $teamChart = $serializer->denormalize(
                $row,
                'App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamChart'
            );
            $teamChart->setTeam(
                $this->em->getReference('App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team', $row['idTeam'])
            );
            $teamChart->setChart($chart);

            $this->em->persist($teamChart);
        }

        $this->em->flush();

        $this->bus->dispatch(
            new UpdateTeamGroupRank($chart->getGroup()->getId()),
            [
                new DescriptionStamp(
                    sprintf('Update team-ranking for group [%d]', $chart->getGroup()->getId())
                )
            ]
        );
    }
}
