<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\MasterBadge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Game\LatestGames;
use Symfony\Contracts\Cache\CacheInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Game::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Game::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Game::class)]
class GameListener
{
    /** @var array<string, array{0: mixed, 1: mixed}|mixed> */
    private array $changeSet = [];

    public function __construct(
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * @param Game $game
     */
    public function prePersist(Game $game): void
    {
        if (null == $game->getLibGameFr()) {
            $game->setLibGameFr($game->getLibGameEn());
        }

        $badge = new MasterBadge();
        $badge->setPicture('master_default.gif');
        $game->setBadge($badge);
    }

    /**
     * @param Game $game
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Game $game, PreUpdateEventArgs $event): void
    {
        $this->changeSet = $event->getEntityChangeSet();

        if ($game->getGameStatus()->isActive() && ($game->getPublishedAt() == null)) {
            $game->setPublishedAt(new DateTime());
            $this->cache->delete(LatestGames::CACHE_KEY);
        }
    }

    /**
     * @param Game $game
     * @param LifecycleEventArgs $event
     * @phpstan-param LifecycleEventArgs<EntityManagerInterface> $event
     */
    public function postUpdate(Game $game, LifecycleEventArgs $event): void
    {
        $em = $event->getObjectManager();

        if (array_key_exists('serie', $this->changeSet)) {
            $this->majSerie($this->changeSet['serie'][0]);
            $this->majSerie($this->changeSet['serie'][1]);
        }

        $em->flush();
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
