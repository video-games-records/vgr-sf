<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Application\DataProvider\Core;

use DateTime;
use Doctrine\DBAL\Exception;

class TeamDataProvider extends AbstractCoreDataProvider
{
    /**
     * @return array<mixed>
     * @throws Exception
     */
    public function getData(): array
    {
        $conn = $this->em->getConnection();
        $sql = "SELECT t.id,
                   t.point_chart,
                   t.point_badge,
                   t.chart_rank0,
                   t.chart_rank1,
                   t.chart_rank2,
                   t.chart_rank3,
                   t.rank_point_chart,
                   t.rank_medal,
                   t.rank_badge,
                   t.rank_cup,
                   t.game_rank0,
                   t.game_rank1,
                   t.game_rank2,
                   t.game_rank3,
                   t.nb_master_badge,
                   t.point_game,
                   t.rank_point_game                  
            FROM vgr_team t";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }

    /** @return array<int, int> */
    public function getNbPostDay(DateTime $date1, DateTime $date2): array
    {
        $query = $this->em->createQuery(
            "
            SELECT
                 t.id,
                 COUNT(pc.id) as nb
            FROM App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart pc
            JOIN pc.player p
            JOIN p.team t
            WHERE pc.lastUpdate BETWEEN :date1 AND :date2
            GROUP BY t.id"
        );

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
