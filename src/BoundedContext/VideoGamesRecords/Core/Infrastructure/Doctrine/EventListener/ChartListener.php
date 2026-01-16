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
     * @param Chart       $chart
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Chart $chart, LifecycleEventArgs $event): void
    {
        if (null == $chart->getLibChartFr()) {
            $chart->setLibChartFr($chart->getLibChartEn());
        }
        $chart->getGroup()->setNbChart($chart->getGroup()->getNbChart() + 1);
        $chart->getGroup()->getGame()->setNbChart($chart->getGroup()->getGame()->getNbChart() + 1);
    }


    /**
     * @param Chart       $chart
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Chart $chart, PreUpdateEventArgs $event): void
    {
        if (null == $chart->getLibChartFr()) {
            $chart->setLibChartFr($chart->getLibChartEn());
        }
    }


    /**
     * @param Chart       $chart
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Chart $chart, LifecycleEventArgs $event): void
    {
        $chart->getGroup()->setNbChart($chart->getGroup()->getNbChart() - 1);
        $chart->getGroup()->getGame()->setNbChart($chart->getGroup()->getGame()->getNbChart() - 1);
    }
}
