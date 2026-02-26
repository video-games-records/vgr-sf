<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Application\DataProvider\Core;

use DateTime;

class GameDataProvider extends AbstractCoreDataProvider
{
    /** @return array<mixed> */
    public function getData(): array
    {
        return $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game')
            ->findAll();
    }

    /** @return array<int, int> */
    public function getNbPostDay(DateTime $date1, DateTime $date2): array
    {
        //----- data nbPostDay
        $query = $this->em->createQuery(
            "
            SELECT
                 ga.id,
                 COUNT(pc.id) as nb
            FROM App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart pc
            JOIN pc.chart c
            JOIN c.group gr
            JOIN gr.game ga
            WHERE pc.lastUpdate BETWEEN :date1 AND :date2
            GROUP BY ga.id"
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
