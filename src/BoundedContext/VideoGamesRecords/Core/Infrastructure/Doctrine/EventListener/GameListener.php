<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs as BaseLifecycleEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Game::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Game::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Game::class)]
#[AsEntityListener(event: Events::postLoad, method: 'postLoad', entity: Game::class)]
class GameListener
{
    /** @var array<string, array{0: mixed, 1: mixed}> */
    private array $changeSet = [];

    public function __construct(private RequestStack $requestStack)
    {
    }

    /**
     * @param Game                   $game
     * @param BaseLifecycleEventArgs $event
     */
    public function prePersist(Game $game, BaseLifecycleEventArgs $event): void
    {
        if (null == $game->getLibGameFr()) {
            $game->setLibGameFr($game->getLibGameEn());
        }

        if ($game->getBadge() === null) {
            $badge = new Badge();
            $badge->setType(BadgeType::MASTER);
            $badge->setPicture('master_default.gif');
            $game->setBadge($badge);
        }
    }

    /**
     * @param Game               $game
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Game $game, PreUpdateEventArgs $event): void
    {
        $this->changeSet = $event->getEntityChangeSet();

        if ($game->getGameStatus()->isActive() && ($game->getPublishedAt() == null)) {
            $game->setPublishedAt(new DateTime());
        }
    }

    /**
     * @param Game                   $game
     * @param BaseLifecycleEventArgs $event
     */
    public function postUpdate(Game $game, BaseLifecycleEventArgs $event): void
    {
        $em = $event->getObjectManager();

        if (array_key_exists('serie', $this->changeSet)) {
            $this->majSerie($this->changeSet['serie'][0]);
            $this->majSerie($this->changeSet['serie'][1]);
        }

        $em->flush();
    }

    /**
     * @param Game $game
     * @param LifecycleEventArgs $event
     */
    public function postLoad(Game $game, LifecycleEventArgs $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $game->getSerie()?->setCurrentLocale($request->getLocale());
        }
    }


    /**
     * @param Serie|null $serie
     * @return void
     */
    private function majSerie(?Serie $serie): void
    {
        if (null === $serie) {
            return;
        }

        $serie->setNbGame(count($serie->getGames()));
        $nbChart = 0;
        foreach ($serie->getGames() as $game) {
            $nbChart += $game->getNbChart();
        }
        $serie->setNbChart($nbChart);
    }
}
