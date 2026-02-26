<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Application\MessageHandler;

use App\BoundedContext\VideoGamesRecords\Dwh\Application\DataProvider\Core\PlayerDataProvider;
use App\BoundedContext\VideoGamesRecords\Dwh\Application\Message\UpdatePlayer;
use App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Player as DwhPlayer;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class UpdatePlayerHandler
{
    public function __construct(
        private PlayerDataProvider $playerDataProvider,
        #[Autowire(service: 'doctrine.orm.dwh_entity_manager')]
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(UpdatePlayer $message): void
    {
        $date1 = new DateTime();
        $date1->sub(new DateInterval('P1D'));
        $date2 = new DateTime();

        $data1 = $this->playerDataProvider->getNbPostDay($date1, $date2);
        $data2 = $this->playerDataProvider->getDataRank();
        $list = $this->playerDataProvider->getData();

        foreach ($list as $row) {
            $idPlayer = $row['id'];
            $dwhPlayer = new DwhPlayer();
            $dwhPlayer->setDate($date1->format('Y-m-d'));
            $dwhPlayer->setId($row['id']);
            $dwhPlayer->setChartRank0($row['chart_rank0']);
            $dwhPlayer->setChartRank1($row['chart_rank1']);
            $dwhPlayer->setChartRank2($row['chart_rank2']);
            $dwhPlayer->setChartRank3($row['chart_rank3']);
            $dwhPlayer->setPointChart($row['point_chart']);
            $dwhPlayer->setRankPointChart($row['rank_point_chart']);
            $dwhPlayer->setRankMedal($row['rank_medal']);
            $dwhPlayer->setNbChart($row['nb_chart']);
            $dwhPlayer->setPointGame($row['point_game']);
            $dwhPlayer->setRankPointGame($row['rank_point_game']);
            $dwhPlayer->setNbPostDay((isset($data1[$idPlayer])) ? $data1[$idPlayer] : 0);
            if (isset($data2[$idPlayer])) {
                foreach ($data2[$idPlayer] as $key => $value) {
                    $dwhPlayer->setChartRank($key, $value);
                }
            }
            $this->em->persist($dwhPlayer);
            $this->em->flush();
        }

        $this->em->flush();
    }
}
