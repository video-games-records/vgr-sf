<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerSerieRank;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\SerieStatus;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Serie::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Serie::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Serie::class)]
class SerieListener
{
    /** @var array<string, array{0: mixed, 1: mixed}> */
    private array $changeSet = [];

    public function __construct(private MessageBusInterface $bus)
    {
    }

    /**
     * @param Serie $serie
     */
    public function prePersist(Serie $serie): void
    {
        $badge = new Badge();
        $badge->setType(BadgeType::SERIE);
        $badge->setPicture('default.gif');
        $serie->setBadge($badge);
    }

    /**
     * @param Serie $serie
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Serie $serie, PreUpdateEventArgs $event): void
    {
        $this->changeSet = $event->getEntityChangeSet();
    }

    /**
     * @param Serie $serie
     * @param LifecycleEventArgs $event
     * @phpstan-param LifecycleEventArgs<EntityManagerInterface> $event
     * @throws ExceptionInterface
     */
    public function postUpdate(Serie $serie, LifecycleEventArgs $event): void
    {
        $em = $event->getObjectManager();

        if (
            array_key_exists('status', $this->changeSet)
            && $this->changeSet['status'][1] == SerieStatus::ACTIVE
        ) {
            $this->bus->dispatch(new UpdatePlayerSerieRank((int) $serie->getId()));
        }

        $em->flush();
    }
}
