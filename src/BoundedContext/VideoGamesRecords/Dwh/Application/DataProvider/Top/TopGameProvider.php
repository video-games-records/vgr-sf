<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Application\DataProvider\Top;

use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository as CoreGameRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\RankingTools;
use App\BoundedContext\VideoGamesRecords\Dwh\Infrastructure\Doctrine\Repository\GameRepository as DwhGameRepository;

class TopGameProvider extends AbstractTopProvider
{
    /** @return array<mixed> */
    public function getTop(
        DateTime $date1Begin,
        DateTime $date1End,
        DateTime $date2Begin,
        DateTime $date2End,
        int $limit = 20
    ): array {
        /** @var DwhGameRepository $dwhGameRepository */
        $dwhGameRepository = $this->dwhEntityManager->getRepository('App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Game');

        /** @var CoreGameRepository $coreGameRepository */
        $coreGameRepository = $this->defaultEntityManager->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game');

        $gameList1 = $dwhGameRepository->getTop(
            $date1Begin,
            $date1End,
            $limit
        );
        $gameList2 = $dwhGameRepository->getTop(
            $date2Begin,
            $date2End,
            $limit
        );

        // Get old rank
        $oldRank = [];
        foreach ($gameList2 as $key => $row) {
            $oldRank[$row['id']] = $key + 1;
        }

        $nbPostFromList = 0;
        for ($i = 0, $nb = count($gameList1) - 1; $i <= $nb; ++$i) {
            $idGame = $gameList1[$i]['id'];
            if (isset($oldRank[$idGame])) {
                $gameList1[$i]['oldRank'] = $oldRank[$idGame];
            } else {
                $gameList1[$i]['oldRank'] = null;
            }

            $game = $coreGameRepository->find($idGame);
            $gameList1[$i]['game'] = $game;
            $nbPostFromList += $gameList1[$i]['nb'];
        }

        $nbGame =  0;
        try {
            $nbGame = $dwhGameRepository->getTotalNbGame($date1Begin, $date1End);
        } catch (NoResultException | NonUniqueResultException $e) {
            // OK
        }

        $nbTotalPost = 0;
        try {
            $nbTotalPost = $dwhGameRepository->getTotalNbPostDay($date1Begin, $date1End);
        } catch (NoResultException | NonUniqueResultException $e) {
            // OK
        }

        $gameList = RankingTools::addRank(
            $gameList1,
            'rank',
            ['nb'],
            true
        );

        return [
            'list' => $gameList,
            'nbPostFromList' => $nbPostFromList,
            'nbItem' => $nbGame,
            'nbTotalPost' => $nbTotalPost,
        ];
    }
}
