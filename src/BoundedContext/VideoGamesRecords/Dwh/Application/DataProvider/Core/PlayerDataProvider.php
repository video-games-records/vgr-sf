<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Application\DataProvider\Core;

use DateTime;
use Doctrine\DBAL\Exception;

class PlayerDataProvider extends AbstractCoreDataProvider
{
    /**
     * @return array<mixed>
     * @throws Exception
     */
    public function getData(): array
    {
        $conn = $this->em->getConnection();
        $sql = "SELECT p.id,
                   p.chart_rank0,
                   p.chart_rank1,
                   p.chart_rank2,
                   p.chart_rank3,
                   p.point_chart,
                   p.rank_point_chart,
                   p.rank_medal,
                   p.nb_chart,
                   p.point_game,
                   p.rank_point_game                   
            FROM vgr_player p
            WHERE p.id <> 0";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }

    /** @return array<int, array<int, int>> */
    public function getDataRank(): array
    {
        $query = $this->em->createQuery("
                    SELECT
                         p.id,
                         CASE WHEN pc.rank > 29 THEN 30 ELSE pc.rank END AS rank,
                         COUNT(pc.id) as nb
                    FROM App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart pc
                    JOIN pc.player p
                    WHERE pc.rank > 3            
                    GROUP BY p.id, rank");

        $result = $query->getResult();
        $data = [];
        foreach ($result as $row) {
            $data[$row['id']][$row['rank']] = $row['nb'];
        }
        return $data;
    }

    /** @return array<int, int> */
    public function getNbPostDay(DateTime $date1, DateTime $date2): array
    {
        $query = $this->em->createQuery("
            SELECT
                 p.id,
                 COUNT(pc.chart) as nb
            FROM App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart pc
            JOIN pc.player p
            WHERE pc.lastUpdate BETWEEN :date1 AND :date2
            GROUP BY p.id");


        $query->setParameter('date1', $date1);
        $query->setParameter('date2', $date2);
        $result = $query->getResult();

        $data = [];
        foreach ($result as $row) {
            $data[$row['id']] = $row['nb'];
        }
        return $data;
    }
}
