<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Chart::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Chart::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Chart::class)]
class ChartListener
{
    /**
     * @param Chart $chart
     */
    public function prePersist(Chart $chart): void
    {
        if (null == $chart->getLibChartFr()) {
            $chart->setLibChartFr($chart->getLibChartEn());
        }
        $group = $chart->getGroup();
        $game = $group->getGame();
        $group->setNbChart($group->getNbChart() + 1);
        $game->setNbChart($game->getNbChart() + 1);
        if (!$group->getIsDlc()) {
            $game->setNbChartWithoutDlc($game->getNbChartWithoutDlc() + 1);
        }
    }


    /**
     * @param Chart $chart
     */
    public function preUpdate(Chart $chart): void
    {
        if (null == $chart->getLibChartFr()) {
            $chart->setLibChartFr($chart->getLibChartEn());
        }
    }


    /**
     * @param Chart $chart
     */
    public function preRemove(Chart $chart): void
    {
        $group = $chart->getGroup();
        $game = $group->getGame();
        $group->setNbChart($group->getNbChart() - 1);
        $game->setNbChart($game->getNbChart() - 1);
        if (!$group->getIsDlc()) {
            $game->setNbChartWithoutDlc($game->getNbChartWithoutDlc() - 1);
        }
    }
}
