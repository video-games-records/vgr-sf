<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\EventSubscriber;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Event\LostPositionEvent;

final readonly class LostPositionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LostPositionEvent::class => 'handleLostPosition',
        ];
    }

    /**
     * @throws ORMException
     */
    public function handleLostPosition(LostPositionEvent $event): void
    {
        $playerChart = $event->getPlayerChart();
        $oldRank = $event->getPreviousRank();
        $newRank = $event->getCurrentRank();
        $oldNbEqual = $event->getPreviousNbEqual();
        $newNbEqual = $playerChart->getNbEqual();

        // Same logic as the old PlayerChartUpdatedSubscriber
        if (
            (($oldRank >= 1) && ($oldRank <= 3) && ($newRank > $oldRank)) ||
            (($oldRank === 1) && ($oldNbEqual === 1) && ($newRank === 1) && ($newNbEqual > 1))
        ) {
            $lostPosition = new \App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\LostPosition();
            $lostPosition->setNewRank($newRank);
            $lostPosition->setOldRank(($oldNbEqual == 1 && $oldRank == 1) ? 0 : $oldRank);
            /** @var Player $player */
            $player = $this->em->getReference(
                'App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player',
                $playerChart->getPlayer()->getId()
            );
            $lostPosition->setPlayer($player);
            /** @var Chart $chart */
            $chart = $this->em->getReference(
                'App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart',
                $playerChart->getChart()->getId()
            );
            $lostPosition->setChart($chart);
            $this->em->persist($lostPosition);
        }
    }
}
