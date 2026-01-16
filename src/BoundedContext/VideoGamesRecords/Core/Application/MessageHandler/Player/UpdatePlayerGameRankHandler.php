<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\MessageHandler\Player;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge;
use App\SharedKernel\Domain\Exception\EntityNotFoundException;
use DateMalformedStringException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGame;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerData;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerGameRank;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerPlatformRank;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerRank;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerSerieRank;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\RankingTools;
use Zenstruck\Messenger\Monitor\Stamp\DescriptionStamp;

#[AsMessageHandler]
readonly class UpdatePlayerGameRankHandler
{
    private const int DELAY_SERIE_UPDATE = 3600000; // 1 heure
    private const int DELAY_PLATFORM_UPDATE = 21600000; // 6 heures


    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(UpdatePlayerGameRank $updatePlayerGameRank): void
    {
        /** @var Game|null $game */
        $game = $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game')
            ->find($updatePlayerGameRank->getGameId());
        if (null === $game) {
            throw new EntityNotFoundException('Player', $updatePlayerGameRank->getGameId());
        }

        //----- delete
        $query = $this->em->createQuery(
            'DELETE App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGame pg WHERE pg.game = :game'
        );
        $query->setParameter('game', $game);
        $query->execute();

        //----- data without DLC
        $query = $this->em->createQuery("
            SELECT
                 p.id,
                 SUM(pg.pointChart) as pointChartWithoutDlc,
                 SUM(pg.nbChart) as nbChartWithoutDlc,
                 SUM(pg.nbChartProven) as nbChartProvenWithoutDlc
            FROM App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGroup pg
            JOIN pg.player p
            JOIN pg.group g
            WHERE g.game = :game
            AND g.isDlc = 0
            GROUP BY p.id");

        $dataWithoutDlc = [];

        $query->setParameter('game', $game);
        $result = $query->getResult();
        foreach ($result as $row) {
            $dataWithoutDlc[$row['id']] = $row;
        }

        //----- select and save result in array
        $query = $this->em->createQuery("
            SELECT
                p.id,
                SUM(pg.chartRank0) as chartRank0,
                SUM(pg.chartRank1) as chartRank1,
                SUM(pg.chartRank2) as chartRank2,
                SUM(pg.chartRank3) as chartRank3,
                SUM(pg.chartRank4) as chartRank4,
                SUM(pg.chartRank5) as chartRank5,
                SUM(pg.pointChart) as pointChart
            FROM App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGroup pg
            JOIN pg.player p
            JOIN pg.group g
            WHERE g.game = :game
            AND g.isRank = 1
            GROUP BY p.id");


        $dataRank = [];
        $query->setParameter('game', $game);
        $result = $query->getResult();
        foreach ($result as $row) {
            $dataRank[$row['id']] = $row;
        }

        //----- select and save result in array
        $query = $this->em->createQuery("
            SELECT
                p.id,
                '' as rankPointChart,
                '' as rankMedal,
                SUM(pg.nbChart) as nbChart,
                SUM(pg.nbChartProven) as nbChartProven,
                MAX(pg.lastUpdate) as lastUpdate
            FROM App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGroup pg
            JOIN pg.player p
            JOIN pg.group g
            WHERE g.game = :game
            GROUP BY p.id");


        $query->setParameter('game', $game);
        $result = $query->getResult();

        $list = [];
        foreach ($result as $row) {
            $row['lastUpdate'] = new \DateTime($row['lastUpdate']);
            // $dataWithoutDlc
            if (isset($dataWithoutDlc[$row['id']])) {
                $row = array_merge($row, $dataWithoutDlc[$row['id']]);
            } else {
                $row['pointChartWithoutDlc'] = 0;
                $row['nbChartWithoutDlc'] = 0;
                $row['nbChartProvenWithoutDlc'] = 0;
            }
            // $dataRank
            if (isset($dataRank[$row['id']])) {
                $row = array_merge($row, $dataRank[$row['id']]);
            } else {
                $row['chartRank0'] = 0;
                $row['chartRank1'] = 0;
                $row['chartRank2'] = 0;
                $row['chartRank3'] = 0;
                $row['chartRank4'] = 0;
                $row['chartRank5'] = 0;
                $row['pointChart'] = 0;
            }
            $list[] = $row;
        }

        //----- add some data
        $list = RankingTools::order($list, ['pointChart' => SORT_DESC]);
        $list = RankingTools::addRank($list, 'rankPointChart', ['pointChart'], true);
        $list = RankingTools::calculateGamePoints($list, ['rankPointChart', 'nbEqual'], 'pointGame', 'pointChart');
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
            $playerGame = $serializer->denormalize(
                $row,
                'App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGame'
            );
            $playerGame->setPlayer($this->em->getReference('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player', $row['id']));
            $playerGame->setGame($game);

            $this->em->persist($playerGame);
        }

        //Stats
        $game->setNbPlayer(count($list));

        $this->em->flush();

        if ($game->getSerie()) {
            $this->bus->dispatch(
                new UpdatePlayerSerieRank(
                    $game->getSerie()->getId(),
                ),
                [
                    new DelayStamp(self::DELAY_SERIE_UPDATE),
                    new DescriptionStamp(
                        sprintf('Update player-ranking for serie [%d]', $game->getSerie()->getId())
                    )
                ]
            );
        }

        /** @var Platform $platform */
        foreach ($game->getPlatforms() as $platform) {
            $this->bus->dispatch(
                new UpdatePlayerPlatformRank($platform->getId()),
                [
                    new DelayStamp(self::DELAY_PLATFORM_UPDATE),
                    new DescriptionStamp(
                        sprintf('Update player-ranking for platform [%d]', $platform->getId())
                    )
                ]
            );
        }

        /** @var PlayerGame $playerGame */
        foreach ($game->getPlayerGame() as $playerGame) {
            $this->bus->dispatch(
                new UpdatePlayerData($playerGame->getPlayer()->getId()),
                [
                    new DescriptionStamp(
                        sprintf('Update player-data for player [%d]', $playerGame->getPlayer()->getId())
                    )
                ]
            );
        }

        $this->bus->dispatch(new UpdatePlayerRank());

        // Update badges directly (was in PlayerGameUpdatedSubscriber - now optimized)
        if ($game->getBadge()) {
            // Get first place players from the ranking we just calculated
            $firstPlacePlayers = [];
            foreach ($list as $row) {
                if ($row['rankPointChart'] === 1) {
                    $firstPlacePlayers[$row['id']] = 0;
                } else {
                    break; // Rankings are ordered, so no more first places
                }
            }

            $this->em->getRepository(PlayerBadge::class)
                ->updateBadge($firstPlacePlayers, $game->getBadge());
        }
    }
}
