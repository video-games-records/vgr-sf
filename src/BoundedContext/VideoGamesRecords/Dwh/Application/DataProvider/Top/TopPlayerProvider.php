<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Application\DataProvider\Top;

use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository as CorePlayerRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\RankingTools;
use App\BoundedContext\VideoGamesRecords\Dwh\Infrastructure\Doctrine\Repository\PlayerRepository as DwhPlayerRepository;

class TopPlayerProvider extends AbstractTopProvider
{
    /** @return array<mixed> */
    public function getTop(
        DateTime $date1Begin,
        DateTime $date1End,
        DateTime $date2Begin,
        DateTime $date2End,
        int $limit = 20
    ): array {
        /** @var DwhPlayerRepository $dwhPlayerRepository */
        $dwhPlayerRepository = $this->dwhEntityManager->getRepository('App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Player');

        /** @var CorePlayerRepository $corePlayerRepository */
        $corePlayerRepository = $this->defaultEntityManager->getRepository(
            'App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player'
        );

        $playerList1 = $dwhPlayerRepository->getTop(
            $date1Begin,
            $date1End,
            $limit
        );
        $playerList2 = $dwhPlayerRepository->getTop(
            $date2Begin,
            $date2End,
            $limit
        );

        // Get old rank
        $oldRank = [];
        foreach ($playerList2 as $key => $row) {
            $oldRank[$row['id']] = $key + 1;
        }

        $nbPostFromList = 0;
        for ($i = 0, $nb = count($playerList1) - 1; $i <= $nb; ++$i) {
            $idPlayer = $playerList1[$i]['id'];
            if (isset($oldRank[$idPlayer])) {
                $playerList1[$i]['oldRank'] = $oldRank[$idPlayer];
            } else {
                $playerList1[$i]['oldRank'] = null;
            }
            $player = $corePlayerRepository->find($idPlayer);
            $playerList1[$i]['player'] = $player;
            $nbPostFromList += $playerList1[$i]['nb'];
        }

        $nbPlayer = 0;
        try {
            $nbPlayer = $dwhPlayerRepository->getTotalNbPlayer($date1Begin, $date1End);
        } catch (NoResultException | NonUniqueResultException $e) {
            // OK
        }

        $nbTotalPost = 0;
        try {
            $nbTotalPost = $dwhPlayerRepository->getTotalNbPostDay($date1Begin, $date1End);
        } catch (NoResultException | NonUniqueResultException $e) {
            // OK
        }

        $playerList = RankingTools::addRank(
            $playerList1,
            'rank',
            ['nb'],
            true
        );

        return [
            'list' => $playerList,
            'nbPostFromList' => $nbPostFromList,
            'nb' => $nbPlayer,
            'nbTotalPost' => $nbTotalPost,
        ];
    }
}
