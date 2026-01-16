<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Manager;

use Doctrine\ORM\EntityManagerInterface;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;

readonly class GameManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }


    /**
     * @param Game $game
     */
    public function copy(Game $game): void
    {
        $newGame = new Game();
        $newGame->setLibGameEn($game->getLibGameEn() . ' [COPY]');
        $newGame->setLibGameFr($game->getLibGameFr() . ' [COPY]');
        $newGame->setSerie($game->getSerie());
        $newGame->setPicture($game->getPicture());

        $badge = new Badge();
        $badge->setType(BadgeType::MASTER);
        $badge->setPicture($game->getBadge()->getPicture());
        $newGame->setBadge($badge);

        foreach ($game->getPlatforms() as $platform) {
            $newGame->addPlatform($platform);
        }

        /** @var Group $group */
        foreach ($game->getGroups() as $group) {
            $newGroup = new Group();
            $newGroup->setLibGroupEn($group->getLibGroupEn());
            $newGroup->setLibGroupFr($group->getLibGroupFr());
            $newGroup->setIsDlc($group->getIsDlc());

            /** @var Chart $chart */
            foreach ($group->getCharts() as $chart) {
                $newChart = new Chart();
                $newChart->setLibChartEn($chart->getLibChartEn());
                $newChart->setLibChartFr($chart->getLibChartFr());

                /** @var ChartLib $lib */
                foreach ($chart->getLibs() as $lib) {
                    $newLib = new ChartLib();
                    $newLib->setName($lib->getName());
                    $newLib->setType($lib->getType());
                    $newChart->addLib($newLib);
                }
                $newGroup->addChart($newChart);
            }

            $newGame->addGroup($newGroup);
        }
        $this->em->persist($newGame);
        $this->em->flush();
    }


    /**
     * @param Game $game
     * @param bool $isVideoProofOnly
     * @return void
     */
    public function updateVideoProofOnly(Game $game, bool $isVideoProofOnly): void
    {
        foreach ($game->getGroups() as $group) {
            foreach ($group->getCharts() as $chart) {
                $chart->setIsProofVideoOnly($isVideoProofOnly);
            }
        }
        $this->em->flush();
    }
}
